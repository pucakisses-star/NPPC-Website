#!/usr/bin/env bash
#
# BATCH 169 -- durations the sources state in words and the date fields
# never counted.
#
#   THE SHAPE, found by sweeping every case row after the Mark Rudd fix
#   in batch 168: a source records HOW LONG somebody was held but not
#   WHEN, the row carries no usable pair of dates, and the duration
#   calculation returns null. The custody is described in full in prose
#   the reader can see, and counts as zero in the figure printed above
#   it. The row does not look wrong. It looks correctly cautious, which
#   is why no earlier audit caught it.
#
#   The scale: Marshall Conway, about forty-four years, publishing
#   nothing. Rubin Carter, roughly nineteen. Mujahid Farid,
#   thirty-three. Eddie Ellis, twenty-three. Thirty-two rows here,
#   2,205 months, about a hundred and eighty-four years.
#
#   ONE MECHANISM ONLY: imprisoned_for_months. It is the column the
#   archive already has for a documented duration with uncertain
#   endpoints, it outranks date arithmetic in computeImprisonedForDays,
#   and unlike an anchored release date it survives the nightly
#   recompute. Every value is read off the row own prose and nothing
#   else.
#
#   AN EARLIER DRAFT OF THIS BATCH WAS WRONG and the correction is worth
#   recording. It also proposed entering release dates for Trumbo, Cole,
#   Bessie, Saxe and Power, and an anchored release for Gutierrez de
#   Lara. Every one of those rows ALREADY had a release date, at year or
#   month precision, already counting correctly. The sweep had reported
#   them as empty because its date parser expected YYYY-MM-DD and the
#   API emits partial dates truncated to precision — a bare 1892 for a
#   year, 1951-04 for a month. Fifteen records were in the fix list that
#   needed no fix. The bug was in the sweep, not the archive.
#
#   FOUR MORE WERE DROPPED FOR DOUBLE-COUNTING. Pratt has three case
#   rows for one imprisonment and one of them already computes 9,083
#   days; adding his stated 27 years would have published 52. Marzani
#   has two rows for one imprisonment. Braden has two rows giving eight
#   and nine months for what may be the same custody. Aceto served
#   "under three years", which is a ceiling and not a figure.
#
#   FOUR ROWS ARE FLOORS. Where a source says "more than six years" the
#   entry is the six, as the least the custody can have been, and each
#   of those rows gets a sentence saying so. Same convention as Van
#   Lydegraf, whose 773 days are measured to the first day his release
#   year allows.
#
#   MATCHED ON THE PROSE, not on a row index. Several of these prisoners
#   have more than one case, and time attached to the wrong prosecution
#   is worse than time not attached at all. Each fix names the phrase
#   that produced its figure and applies only when exactly one case row
#   on that prisoner contains it. Zero matches or two both skip and say
#   so — that guard is what caught Marzani.
#
#   Idempotent: fixed values, phrase matching, the floor note appended
#   only once.
#
# Run from the repo root, after git pull (after batch 168):
#   bash database/data/run-batch-169.sh
#
# Then, and this matters more than the batch:
#   php artisan prisoners:recompute-imprisonment --apply

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"; shift
    echo; echo "--- ${label}"
    if "$@"; then return 0; fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}"); return 0
}

echo "==================================================================="
echo "  Batch 169 — counting durations the prose states and the dates lost"
echo "==================================================================="

apply_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch169.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

// The phrase that produced each figure has to appear in exactly one case row
// on that prisoner. Several of these people have more than one prosecution,
// and a duration attached to the wrong one is worse than one not attached.
$pick = function ($p, $phrase) {
    $hit = $p->cases->filter(function ($c) use ($phrase) {
        $hay = mb_strtolower(trim(($c->sentence ?? "")." ".($c->charges ?? "")));

        return $hay !== "" && mb_strpos($hay, mb_strtolower($phrase)) !== false;
    });

    return $hit->count() === 1 ? $hit->first() : null;
};

$done = 0; $skipped = 0; $gained = 0; $skips = [];

echo "  ", str_pad("record", 26), str_pad("months", 9), str_pad("days", 18), "source phrase\n";
echo "  ", str_repeat("-", 92), "\n";

foreach ($payload["months"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->with("cases")->first();

    if (! $p) { $skips[] = $row["slug"]." — record not found"; $skipped++; continue; }

    $case = $pick($p, $row["match_phrase"]);

    if (! $case) {
        $skips[] = $row["slug"]." — the phrase matched "
            .($p->cases->count() ? "zero or several case rows" : "no case rows");
        $skipped++;
        continue;
    }

    $was = (int) $case->imprisoned_for_days;

    $case->imprisoned_for_months = $row["months"];

    if (! empty($row["append_note"]) && mb_strpos($case->sentence ?? "", "recorded as a floor") === false) {
        $case->sentence = trim(($case->sentence ?? "").$row["append_note"]);
    }

    $case->save();
    $case->refresh();

    $now = (int) $case->imprisoned_for_days;
    $gained += ($now - $was);

    echo "  ", str_pad($row["slug"], 26),
        str_pad($row["months"].(! empty($row["floor"]) ? " mo+" : " mo"), 9),
        str_pad($was." -> ".$now, 18),
        "\"", $row["match_phrase"], "\"\n";
    $done++;
}

echo "\n  ", str_repeat("=", 92), "\n";
echo "  applied ", $done, ", skipped ", $skipped, "\n";
echo "  days recovered: ", number_format($gained), " (", round($gained / 365.25, 1), " prisoner-years)\n";
echo "  rows marked + are floors: the source said more than that figure, not that figure.\n";

foreach ($skips as $s) { echo "  SKIPPED  ", $s, "\n"; }

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "duration-sweep-fixes" apply_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 169 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "NOW RUN THE RECOMPUTE. It matters more than this batch: the sweep"
echo "also re-measured the stale counters batch 137 diagnosed, and the"
echo "archive is still publishing 460 figures of 45 years or more —"
echo "Thoreau at 180 years for one night in jail. Batch 137 already"
echo "replaced the nightly job that wrote them; it has never been run."
echo
echo "    php artisan prisoners:recompute-imprisonment --apply"
echo
echo "Expect 0 skips here. A skip means a phrase stopped matching exactly"
echo "one case row, which is the guard working rather than a failure."
echo
echo "See database/data/DURATION-SWEEP.md for the nine class-A records"
echo "this batch leaves alone, and for the ten class-B records where the"
echo "dates OVERSTATE — Kathy Kelly currently reads as twenty-six years."
