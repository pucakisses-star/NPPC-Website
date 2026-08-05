#!/usr/bin/env bash
#
# BATCH 168 -- Mark Rudd: the two documented days in December 1969,
# which batch 167 recorded in prose and then failed to count.
#
#   WHAT WAS WRONG. The source states that he spent two days in jail in
#   December 1969 for travelling outside the area his Chicago bail
#   conditions allowed, but gives no admission or release date. Batch
#   167 stored the incarceration at month precision and left the release
#   empty, so the row published no figure at all. The reasoning given
#   there was that a month-precision release resolves to December 1 and
#   would measure zero against a December 1 incarceration.
#
#   THAT REASONING WAS RIGHT ABOUT THE ARITHMETIC AND WRONG ABOUT THE
#   REMEDY. The duration is the sourced fact. The calendar dates are the
#   unknown. Discarding the sourced fact to protect an anchor convention
#   is backwards: move the anchor.
#
#   HOW. A month-precision date has to sit on some day, and the
#   convention here is the 1st. The release is anchored on the 3rd
#   instead, with the precision mirrored from the incarceration so it
#   stays month-level. Nothing displays or serialises that day —
#   formatPartialDate prints December 1969 on the profile,
#   partialDateIso emits 1969-12 through the API, partialDateParts
#   returns a null day. computeImprisonedForDays is the only reader of
#   it, and it now returns the 2 the source states.
#
#   NOT APPLIED TO THE 1968 TOMBS ROWS, deliberately. Those also carry
#   empty release dates, but there the LENGTH of the custody is unknown
#   too, so there is no figure to preserve and nothing to anchor. The
#   distinction is whether the duration is sourced, not whether the
#   dates are missing.
#
#   ONE FRAGILITY, STATED PLAINLY. Editing this row in the admin panel
#   round-trips the release through a month-precision control, which
#   resets the anchor to the 1st and drops the count back to zero
#   without warning. Re-running this batch restores it.
#
#   Idempotent: matched on the December incarceration date, values fixed.
#
# Run from the repo root, after git pull (after batch 167):
#   bash database/data/run-batch-168.sh

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
echo "  Batch 168 — Mark Rudd: counting the two days in December 1969"
echo "==================================================================="

apply_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch168.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withUnderReview()->where("slug", $payload["slug"])->with("cases")->first();

if (! $p) { echo "  ", $payload["slug"], " NOT FOUND — nothing changed.\n"; return; }

echo "  record: ", $p->name, "  [", $p->slug, "]\n";
echo "  total before: ", (int) $p->cases->sum("imprisoned_for_days"), " days across ",
    $p->cases->count(), " row(s)\n";

$want = $payload["match"]["incarceration_date"];

$case = $p->cases->first(function ($c) use ($want) {
    return $c->incarceration_date && $c->incarceration_date->format("Y-m-d") === $want;
});

if (! $case) {
    echo "\n  no case row with incarceration_date ", $want, " — batch 167 may not have run.\n";
    echo "  nothing changed.\n";
    return;
}

echo "\n  row [", $case->id, "]  days before: ", ($case->imprisoned_for_days ?? "null"), "\n";

foreach ($payload["dates"] as $field => $parts) {
    $case->setPartialDate($field, $parts[0], $parts[1] ?? null, $parts[2] ?? null);
}

// setPartialDate stamped the release as day precision because a day was
// given. The day is an anchor, not a fact: copy the month precision across
// from the incarceration so the field displays and serialises as the month
// it is actually known to.
$case->mirrorDatePrecision($payload["mirror_precision"]["from"], $payload["mirror_precision"]["to"]);

$case->sentence = $payload["sentence"];
$case->save();
$case->refresh();

foreach (["incarceration_date", "release_date"] as $f) {
    echo "    ", str_pad($f, 20), $case->formatPartialDate($f),
        "  [", $case->datePrecisionFor($f), "]",
        "  stored ", $case->{$f}->format("Y-m-d"),
        "  api ", $case->partialDateIso($f), "\n";
}

$days = $case->imprisoned_for_days;
$wantDays = $payload["expect_days"];

echo "    ", str_pad("imprisoned_for_days", 20), ($days ?? "null"),
    ($days === $wantDays ? "  (want ".$wantDays.", correct)" : "  !! WANT ".$wantDays), "\n";

foreach ($payload["expect_display"] as $f => $text) {
    $got = $case->formatPartialDate($f);
    echo "    display ", str_pad($f, 20), $got, ($got === $text ? "  ok" : "  !! WANT ".$text), "\n";
}

$p->refresh()->load("cases");

$total = (int) $p->cases->sum("imprisoned_for_days");
$start = $p->cases->map(fn ($c) => $c->incarceration_date ?: $c->arrest_date)->filter()->sort()->first();

echo "\n  total after: ", $total, " days across ", $p->cases->count(), " row(s)\n";
echo "  counter: ", ($total > 0
    ? \App\Support\ImprisonmentDuration::phrase($start, $total,
        \App\Support\ImprisonmentDuration::documentedMonths($p->cases))
    : "(none)"), "\n";
echo "  years: ", implode(", ", $p->getIncarcerationYearsArray()), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "rudd-december-1969" apply_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 168 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "Expected: the December row measures 2 days, both dates still read"
echo "December 1969, and the Rudd total goes from 2 days to 4."
