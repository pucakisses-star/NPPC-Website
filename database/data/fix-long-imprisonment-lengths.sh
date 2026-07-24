#!/usr/bin/env bash
#
# Fix false imprisonment lengths surfaced by database/data/audit-long-imprisonment.sh
# (prisoners whose total recorded time in custody reads as more than 60 years).
#
# imprisoned_for_days is derived in PrisonerCase::saving(): for a case with an
# incarceration date and NO release date, it only counts up to today when the
# prisoner is still flagged in_custody or awaiting_trial. Every runaway 60+ year
# total therefore comes from that flag being left on for someone who is actually
# dead or long since released (the Martin Luther King Jr. bug).
#
# Remedy, applied only where it is unambiguously safe:
#   1. Deceased-in-custody: prisoner has a death_date but is flagged in_custody /
#      awaiting_trial -> clear the flags, mark released, recompute cases.
#   2. Impossible open-case length: prisoner flagged in custody but a SINGLE open
#      case already exceeds ~60 years to today -> no living person has been
#      continuously detained that long, so the person is dead/released -> clear
#      the flags, mark released, recompute cases.
#   3. Stale open cases on a not-in-custody prisoner -> re-save so the hook nulls
#      the leftover count. No flag change.
#
# Deliberately NOT touched (reported for manual review instead):
#   - A currently-flagged prisoner whose 60+ total comes from DUPLICATE open cases
#     each under a lifetime (e.g. a modern lifer entered twice) -- flipping the
#     flag would wrongly mark a genuinely detained person as released.
#   - Real CLOSED overlapping sentences that legitimately sum high (concurrent
#     terms double-counted) -- that is a de-duplication problem, not this bug.
#
# Idempotent: once fixed, those totals fall below the threshold and are skipped
# on re-runs. Run from the repo root:
#   bash database/data/fix-long-imprisonment-lengths.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Carbon\Carbon;

$threshold = 60 * 365;          // days
$maxHumanYears = 60;            // a single continuous detention beyond this is impossible for a living person
$today = Carbon::today();

$fixedDeceased = 0; $fixedImpossible = 0; $recalc = 0; $review = 0;
$reviewList = [];

$sumDays = function ($p) {
    return (int) $p->cases->pluck("imprisoned_for_days")
        ->filter(function ($d) { return $d !== null; })->sum();
};

foreach (Prisoner::withoutGlobalScopes()->with(["cases"])->get() as $p) {
    $total = $sumDays($p);
    if ($total <= $threshold) { continue; }

    $hasOpen = false;
    $maxOpenYears = 0.0;
    foreach ($p->cases as $c) {
        if ($c->incarceration_date && ! $c->release_date) {
            $hasOpen = true;
            $yrs = Carbon::parse($c->incarceration_date)->diffInDays($today) / 365.25;
            if ($yrs > $maxOpenYears) { $maxOpenYears = $yrs; }
        }
    }

    $flagged = $p->in_custody || $p->awaiting_trial;
    $flip = false; $why = "";

    if ($flagged && ! empty($p->death_date)) {
        $flip = true; $why = "deceased-in-custody";
    } elseif ($flagged && $maxOpenYears > $maxHumanYears) {
        $flip = true; $why = "impossible open-case length (~".round($maxOpenYears)." yrs)";
    }

    if ($flip) {
        $p->in_custody = false;
        $p->awaiting_trial = false;
        $p->released = true;
        $p->save();
        foreach ($p->cases as $c) { $c->setRelation("prisoner", $p); $c->save(); }
        $newTotal = $sumDays($p);
        if (str_starts_with($why, "deceased")) { $fixedDeceased++; } else { $fixedImpossible++; }
        echo "FIXED  [".$why."] ".$p->slug." | ".$p->name." | ".round($total / 365.25, 1)." -> ".round($newTotal / 365.25, 1)." yrs\n";
    } elseif (! $flagged && $hasOpen) {
        foreach ($p->cases as $c) { $c->setRelation("prisoner", $p); $c->save(); }
        $newTotal = $sumDays($p);
        $recalc++;
        echo "RECALC [stale open-case] ".$p->slug." | ".$p->name." | ".round($total / 365.25, 1)." -> ".round($newTotal / 365.25, 1)." yrs\n";
    } else {
        $review++;
        $reviewList[] = "  ".$p->slug." | ".$p->name." | ".round($total / 365.25, 1)." yrs".($flagged ? " [still flagged in_custody/awaiting_trial]" : " [closed cases sum high]");
    }
}

echo "\n=== Summary ===\n";
echo "Fixed (deceased-in-custody): {$fixedDeceased}\n";
echo "Fixed (impossible open-case length): {$fixedImpossible}\n";
echo "Recomputed (stale open cases): {$recalc}\n";
echo "Left for manual review: {$review}\n";
if ($reviewList) {
    echo "\nManual review (not modified -- genuine detention with duplicate cases, or real overlapping closed sentences):\n";
    echo implode("\n", $reviewList)."\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done. Long-imprisonment false lengths fixed."
