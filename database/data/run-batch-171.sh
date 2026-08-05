#!/usr/bin/env bash
#
# BATCH 171 -- birth years worked back from ages the descriptions already
# state, for 151 records that carry no birthdate at all.
#
#   THE ARITHMETIC. An age of N reported on a known day D means a birth
#   in the 365-day window D - (N+1) years + 1 day .. D - N years. That
#   window always straddles two calendar years, so the answer is a
#   likelier year rather than a known one. Every value here is stored at
#   circa precision, which renders "c. 1974" and means plus or minus
#   one. HasPartialDates documents that precision for exactly this case.
#
#   ON THE CONFIDENCE FIGURES, which look alarming and are not. The
#   chosen year is right between 51 and 99 percent of the time, mean 73.
#   That percentage is about WHICH OF TWO ADJACENT YEARS to print, not
#   about whether the answer is roughly right. "c. 1974" at 54 percent
#   means the person was born in 1973 or 1974, which is precisely what
#   "c." claims. The alternative on offer is an empty field.
#
#   FOUR WAYS THIS GOES WRONG, each of which cost a rewrite of the sweep
#   before anything was written here:
#
#   1. THE AGE BELONGS TO SOMEONE ELSE. Ola Mae Davis is described as an
#      elderly, blind, diabetic woman whose case involved police
#      shooting "a 16-year-old Black youth". A loose match reads that 16
#      as hers and files her as born c. 1960. Appositive ages are now
#      only read from the opening clause, where the subject of the
#      sentence is the person the record is about.
#
#   2. THE AGE IS PINNED TO ANOTHER EVENT. "Came from the Philippines
#      with his family in 2001 at age 14", anchored to a 2025 arrest, is
#      out by twenty-four years. Rejected whenever a competing anchor
#      verb or a stray four-digit year sits near the age. (Vijandre is
#      in this batch anyway, at c. 1987, from the "is a 38-year-old" in
#      his opening clause — the right age, not the migration one.)
#
#   3. THE ANCHOR IS NOT A DAY. The API emits partial dates truncated to
#      precision, so a year-precision arrest arrives as "1979" and
#      parses to January 1. A January 1 anchor puts the entire window
#      inside one calendar year and reports ~100 percent confidence in
#      what is really a coin toss. Only day-precision anchors are used,
#      and January 1-2 anchors are dropped too, because that is the
#      value a year-precision date collapses to. It costs a handful of
#      genuine New Year arrests and is worth it.
#
#   4. ^ WITH re.MULTILINE matches the start of every line rather than
#      the start of the description, which quietly turned the
#      opening-clause rule into no rule at all.
#
#   THE SCRIPT DOES NOT TRUST THE PAYLOAD. For each row it re-reads the
#   arrest date off the record, recomputes the window and the likelier
#   year in PHP, and skips the row if its own answer disagrees with the
#   one the sweep produced. It also refuses any record that already has
#   a birthdate, so a curator entry always outranks a derived one.
#
#   FORTY MORE ARE HELD BACK and listed at the end of the run: 26 with
#   no arrest date at all, 7 whose only date is year- or
#   month-precision, 6 anchored on January 1-2, and one where a present
#   tense "is a 21-year-old" sits against a 2020 arrest, which is the
#   age when the entry was written rather than at the arrest.
#
#   Idempotent: fixed values, and existing birthdates are never touched.
#
# Run from the repo root, after git pull (after batch 170):
#   bash database/data/run-batch-171.sh

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
echo "  Batch 171 — birth years from stated ages (151 records)"
echo "==================================================================="

apply_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch171.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

// Recomputed here rather than taken from the payload, so the figure written to
// the database is one this script derived from the date the database holds.
$derive = function (Carbon $anchor, int $age) {
    $hi = $anchor->copy()->subYears($age);
    $lo = $anchor->copy()->subYears($age + 1)->addDay();
    $span = $lo->diffInDays($hi) + 1;
    $early = $lo->diffInDays(Carbon::create($lo->year, 12, 31)) + 1;
    $late = $span - $early;

    return $early >= $late
        ? [$lo->year, $early / $span, $lo, $hi]
        : [$hi->year, $late / $span, $lo, $hi];
};

$done = 0; $skipped = 0; $notes = [];

echo "  ", str_pad("record", 32), str_pad("age", 5), str_pad("anchor", 13),
    str_pad("born", 10), "conf\n";
echo "  ", str_repeat("-", 76), "\n";

foreach ($payload["rows"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->with("cases")->first();

    if (! $p) { $notes[] = $row["slug"]." — record not found"; $skipped++; continue; }

    // A curated birthdate always outranks one worked back from an age.
    if ($p->birthdate) {
        $notes[] = $row["slug"]." — already has a birthdate (".$p->formatPartialDate("birthdate")."), left alone";
        $skipped++;
        continue;
    }

    // The anchor has to still be a real, day-precision arrest on this record.
    $anchor = null;

    foreach ($p->cases as $c) {
        foreach (["arrest_date", "incarceration_date"] as $f) {
            if ($c->{$f}
                && $c->datePrecisionFor($f) === "day"
                && $c->{$f}->format("Y-m-d") === $row["anchor"]) {
                $anchor = $c->{$f}->copy();
                break 2;
            }
        }
    }

    if (! $anchor) {
        $notes[] = $row["slug"]." — no day-precision case date matching ".$row["anchor"]." any more";
        $skipped++;
        continue;
    }

    [$year, $share, $lo, $hi] = $derive($anchor, (int) $row["age"]);

    if ($year !== (int) $row["year"]) {
        $notes[] = $row["slug"]." — recomputed ".$year." but the payload says ".$row["year"].", skipped";
        $skipped++;
        continue;
    }

    $p->setPartialDate("birthdate", $year, null, null, true);
    $p->save();
    $p->refresh();

    echo "  ", str_pad($row["slug"], 32), str_pad($row["age"], 5),
        str_pad($row["anchor"], 13), str_pad($p->formatPartialDate("birthdate"), 10),
        round($share * 100), "%\n";

    if ($p->datePrecisionFor("birthdate") !== "circa") {
        $notes[] = $row["slug"]." — precision came out as ".$p->datePrecisionFor("birthdate").", wanted circa";
    }

    $done++;
}

echo "\n  ", str_repeat("=", 76), "\n";
echo "  applied ", $done, ", skipped ", $skipped, "\n";

foreach ($notes as $n) { echo "  NOTE  ", $n, "\n"; }

echo "\n  HELD BACK BY THE SWEEP (", count($payload["held_back"]), ") — no birth year derived:\n";

$why = [];

foreach ($payload["held_back"] as $h) {
    $k = preg_replace("/\s*[—(].*$/u", "", $h["why"]);
    $why[$k] = ($why[$k] ?? 0) + 1;
}

foreach ($why as $k => $n) { echo "    ", str_pad($n, 5), $k, "\n"; }

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "ages-to-birth-years" apply_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 171 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "Every date should render with a \"c.\" in front of it. A bare year on"
echo "any of these would be the one thing this batch is trying not to say:"
echo "that anybody recorded when these people were born."
