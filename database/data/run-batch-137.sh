#!/usr/bin/env bash
#
# BATCH 137 -- retire cases:update-imprisoned-days and rewrite the
# counters it fabricated.
#
#   THE BUG. The nightly job reimplemented both duration calculations
#   instead of calling the model, so it had none of the guards that
#   live there. It took every case row with an incarceration date and
#   no release date and counted it to today — whether or not anybody
#   was still being held, and whether or not the person was still
#   alive. It then wrote the result with saveQuietly(), which skips
#   the model hook that would have corrected it.
#
#   WHAT IT PUBLISHED. 613 case rows across 596 people carry an
#   imprisonment counter measured to the present for a custody that
#   ended decades or centuries ago:
#
#     julia-emory         "Imprisoned For 323 years"  (jailed 1917,
#                          suffrage picket, Occoquan Workhouse)
#     bradford-lyttle     "Imprisoned For 293 years"
#
#   and 757 people publish an exile counter with the exile flag off:
#
#     thornton-blackburn  193 years   (escaped slavery in 1833)
#     shadrach-minkins    175 years
#     bill-haywood        119 years
#
#   THE FIX is a schedule swap, not new arithmetic.
#   prisoners:recompute-imprisonment already exists and already calls
#   PrisonerCase::computeImprisonedForDays() and
#   computeInExileForDays() — the same two methods the model's saving
#   hook calls — so the nightly job and an ordinary admin save can no
#   longer disagree. app/Console/Kernel.php now schedules it, and
#   app/Console/Commands/UpdateImprisonedDays.php is deleted.
#
#   WHAT CHANGES ON THE SITE. Those ~1,350 profiles stop showing a
#   duration. They do not show a smaller one — they show none, because
#   the honest answer for a custody with no recorded end and nobody
#   still inside is that the length is unknown. Every counter that
#   rests on two real dates is untouched.
#
#   This is the prerequisite for batch 138: four of the six IWW
#   records there have a release date that the sources do not
#   establish, and leaving that column empty is only safe once the
#   nightly job stops reading an empty release date as "still inside".
#
#   The script runs the recompute dry first and prints everything it
#   would change, then applies. Re-running is harmless: the second run
#   reports nothing stale.
#
# Run from the repo root, after git pull (after batch 136):
#   bash database/data/run-batch-137.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"
    shift
    echo
    echo "--- ${label}"
    if "$@"; then
        return 0
    fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}")
    return 0
}

echo "==================================================================="
echo "  Batch 137 — retire the counter-fabricating nightly job"
echo "==================================================================="

show_worst() {
    php artisan tinker --execute='
use App\Models\Prisoner;

echo "The counters as they stand right now.\n\n";

$rows = Prisoner::withUnderReview()->with("cases")->get();

$fake = $rows->filter(function ($p) {
    if ($p->in_custody || $p->awaiting_trial) { return false; }

    return $p->cases->contains(fn ($c) => $c->incarceration_date
        && ! $c->release_date
        && $c->imprisoned_for_days > 0);
});

$exile = $rows->filter(fn ($p) => ! $p->currently_in_exile
    && $p->cases->sum("in_exile_for_days") > 0);

echo "  imprisonment counters on people nobody is holding: ", $fake->count(), "\n";

foreach ($fake->sortByDesc(fn ($p) => $p->cases->sum("imprisoned_for_days"))->take(5) as $p) {
    echo "    ", str_pad($p->slug, 28), " ",
        round($p->cases->sum("imprisoned_for_days") / 365.25, 1), " years\n";
}

echo "\n  exile counters with the exile flag off: ", $exile->count(), "\n";

foreach ($exile->sortByDesc(fn ($p) => $p->cases->sum("in_exile_for_days"))->take(5) as $p) {
    echo "    ", str_pad($p->slug, 28), " ",
        round($p->cases->sum("in_exile_for_days") / 365.25, 1), " years\n";
}
'
}

recompute_dry() {
    php artisan prisoners:recompute-imprisonment
}

recompute_apply() {
    php artisan prisoners:recompute-imprisonment --apply
}

verify() {
    php artisan tinker --execute='
use App\Models\Prisoner;

$rows = Prisoner::withUnderReview()->with("cases")->get();

$fake = $rows->filter(function ($p) {
    if ($p->in_custody || $p->awaiting_trial) { return false; }

    return $p->cases->contains(fn ($c) => $c->incarceration_date
        && ! $c->release_date
        && $c->imprisoned_for_days > 0);
});

$exile = $rows->filter(fn ($p) => ! $p->currently_in_exile
    && $p->cases->sum("in_exile_for_days") > 0);

echo "AFTER\n";
echo "  imprisonment counters on people nobody is holding: ", $fake->count(), " (want 0)\n";
echo "  exile counters with the exile flag off:            ", $exile->count(), " (want 0)\n";

foreach ($fake->take(5) as $p) { echo "    still set: ", $p->slug, "\n"; }
foreach ($exile->take(5) as $p) { echo "    still set (exile): ", $p->slug, "\n"; }

// Counters resting on two real dates must survive untouched.
$kept = Prisoner::withUnderReview()->with("cases")->get()
    ->filter(fn ($p) => $p->cases->contains(fn ($c) => $c->incarceration_date
        && $c->release_date
        && $c->imprisoned_for_days > 0))
    ->count();

echo "  profiles still showing a counter from two recorded dates: ", $kept, "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "before" show_worst
run "recompute-dry-run" recompute_dry
run "recompute-apply" recompute_apply
run "verify" verify

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 137 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
echo
echo "The dry run above lists every case row it rewrote. If a counter"
echo "disappeared that should not have, the row needs a release date —"
echo "that is the missing fact, not the counter."
