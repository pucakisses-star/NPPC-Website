#!/usr/bin/env bash
#
# BATCH 124 -- William Morales exile counter:
#
#   His profile read "Time in Exile: 85 Years 3 Months 21 Days". Two
#   causes, one code and one data.
#
#   The code half ships in this same change (App\Support\ExileDuration):
#   exile time is now the union of a prisoner's exile spans instead of
#   the sum of the per-case day counts, so two open-ended rows can no
#   longer each count the present day.
#
#   The data half is here. His Bellevue row records custody ending
#   May 21, 1979 — an escape, as its own sentence text says, entered in
#   release_date because there is no other column for the day custody
#   ended. The old auto-derive in PrisonerCase::saving() read that as a
#   release into exile and stamped in_exile_since = 1979-05-21 on it.
#   He was not in exile in 1979; he was a fugitive, and from May 1983
#   he was in Mexican custody. His exile begins June 24, 1988, when
#   Mexico put him on a flight to Cuba — which the second case row
#   already records. So the 1979 exile date is cleared, and the second
#   row is left exactly as it is.
#
#   Nothing else is changed. The script also REPORTS (changes nothing)
#   any other prisoner carrying more than one open-ended exile row, so
#   the curator can see whether the same auto-derive misfired
#   elsewhere.
#
#   Idempotent: an already-cleared row just reports as such.
#
# Run from the repo root, after git pull (after batch 123):
#   bash database/data/run-batch-124.sh

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
echo "  Batch 124 — William Morales: drop the 1979 escape exile date"
echo "==================================================================="

fix_morales() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Support\ExileDuration;

$p = Prisoner::withUnderReview()->where("slug", "william-morales")->with("cases")->first();

if (! $p) { echo "Not found: william-morales — nothing changed.\n"; return; }

echo "record: ", $p->name, "  [", $p->slug, "]\n";
echo "  before: ", ExileDuration::totalDays($p->cases), " day(s) in exile across ", $p->cases->count(), " case row(s)\n";

foreach ($p->cases as $c) {
    echo "    case ", $c->id, "  incarcerated=", ($c->incarceration_date ? $c->incarceration_date->format("Y-m-d") : "-"),
        "  released=", ($c->release_date ? $c->release_date->format("Y-m-d") : "-"),
        "  in_exile_since=", ($c->in_exile_since ? $c->in_exile_since->format("Y-m-d") : "-"),
        "  exile_days=", ($c->in_exile_for_days ?? "null"), "\n";
}

// The escape row, identified by the custody end date rather than by
// position, so a reordered or re-imported case set cannot move the edit
// onto the wrong row.
$escape = $p->cases->first(fn ($c) => $c->release_date && $c->release_date->format("Y-m-d") === "1979-05-21");

if (! $escape) {
    echo "\n  No case row ending 1979-05-21 — nothing to clear (already fixed, or the row changed).\n";
} elseif (! $escape->in_exile_since) {
    echo "\n  The 1979-05-21 row already carries no exile date — nothing to do.\n";
} else {
    $was = $escape->in_exile_since->format("Y-m-d");
    $escape->in_exile_since = null;
    $escape->end_of_exile = null;
    $escape->save();   // the saving hook recomputes in_exile_for_days
    echo "\n  Cleared in_exile_since on the escape row (was ", $was, ") — the May 21, 1979 Bellevue escape is not a release into exile.\n";
}

$p->refresh()->load("cases");
$days = ExileDuration::totalDays($p->cases);
echo "  after:  ", $days, " day(s) in exile — ", round($days / 365.25, 1), " years, from ",
    (ExileDuration::startFor($p->cases)?->format("Y-m-d") ?? "-"), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

report_other_multi_exile_records() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Support\ExileDuration;

echo "Records with more than one open-ended exile row (reported only, nothing changed):\n";

$found = 0;

Prisoner::withUnderReview()->with("cases")->chunk(200, function ($chunk) use (&$found) {
    foreach ($chunk as $p) {
        $open = $p->cases->filter(fn ($c) => $c->in_exile_since && ! $c->end_of_exile && $c->in_exile_for_days);

        if ($open->count() < 2) { continue; }

        $found++;
        $naive = (int) $p->cases->sum("in_exile_for_days");
        $union = ExileDuration::totalDays($p->cases);

        echo "  ", $p->slug, "  ", $open->count(), " open rows  (",
            $open->map(fn ($c) => $c->in_exile_since->format("Y-m-d"))->implode(", "), ")",
            "  summed=", $naive, "d  unioned=", $union, "d\n";
    }
});

if ($found === 0) {
    echo "  none.\n";
} else {
    echo "\n  ", $found, " record(s) above were over-counting before this change. The union fix\n";
    echo "  corrects the published figure for all of them; whether any of their exile\n";
    echo "  START dates are themselves wrong is a curatorial question, left alone here.\n";
}
'
}

run "morales-exile-date" fix_morales
run "report-multi-exile-records" report_other_multi_exile_records

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 124 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
