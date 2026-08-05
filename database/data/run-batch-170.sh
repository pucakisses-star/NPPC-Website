#!/usr/bin/env bash
#
# BATCH 170 -- Geoffrey Parsons: a birth year worked back from the age
# his own record already states.
#
#   HE WAS 20 WHEN HE WAS ARRESTED on January 18, 2023, during the
#   Weelaunee forest raid. That puts his birth between January 19, 2002
#   and January 18, 2003: he had turned 20 by the arrest and had not yet
#   turned 21.
#
#   STORED AS CIRCA, NOT AS A FLAT YEAR. The window is 365 days wide and
#   straddles two calendar years, so 2002 is the likelier answer rather
#   than the known one. Recording it as a plain "2002" would assert a
#   precision the source does not support; circa renders "c. 2002",
#   which is exactly what an age gives you.
#
#   AND IT IS A STRONG LIKELIHOOD. 347 of the 365 days in the window
#   fall in 2002 and 18 fall in 2003, so 2002 is right about 95 percent
#   of the time. A mid-January arrest is the near-New-Year case the
#   HasPartialDates comment warns about, but here the calendar helps:
#   the window opens in January and closes the following January, which
#   puts almost all of it in the earlier year. Had he been arrested in
#   July the split would be nearer even and the year would be a guess
#   worth less than the field it filled.
#
#   NOT FIXED HERE, but visible on the same page: his profile reads
#   "Imprisoned For 3 years 6 months 16 days" off a stored 1,293 days.
#   He is flagged released, his case has an incarceration date and no
#   release date, and computeImprisonedForDays returns null for that
#   shape. The 1,293 is residue from the deleted
#   cases:update-imprisoned-days, which counted open-ended cases to
#   whatever day it last ran. The recompute clears it.
#
#   Idempotent: a fixed year at a fixed precision.
#
# Run from the repo root, after git pull (after batch 169):
#   bash database/data/run-batch-170.sh

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
echo "  Batch 170 — Geoffrey Parsons: birth year from a stated age"
echo "==================================================================="

apply_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch170.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withUnderReview()->where("slug", $payload["slug"])->with("cases")->first();

if (! $p) { echo "  ", $payload["slug"], " NOT FOUND — nothing changed.\n"; return; }

echo "  record:  ", $p->name, "  [", $p->slug, "]\n";
echo "  before:  birthdate ", ($p->birthdate ? $p->formatPartialDate("birthdate") : "(none)"),
    "   age ", ($p->age ?? "(none)"), "\n";

// The anchor has to be the date the age was reported ON. Check it against the
// case row rather than trusting the payload: an age is only as good as the
// day it is pinned to, and pinning it to the wrong day moves the birth year.
$at = $payload["age_at"];
$anchor = $p->cases->first(function ($c) use ($at) {
    return $c->arrest_date && $c->arrest_date->format("Y-m-d") === $at["date"];
});

echo "  anchor:  age ", $at["age"], " at ", $at["event"], " on ", $at["date"],
    " — ", ($anchor ? "matches a case row" : "NO CASE ROW WITH THAT ARREST DATE"), "\n";

if (! $anchor) { echo "\n  refusing to derive a birth year from an unanchored age.\n"; return; }

$b = $payload["birthdate"];
$p->setPartialDate("birthdate", $b[0], $b[1] ?? null, $b[2] ?? null, (bool) ($payload["approximate"] ?? false));
$p->save();
$p->refresh();

$shown = $p->formatPartialDate("birthdate");
$prec = $p->datePrecisionFor("birthdate");

echo "\n  after:   birthdate ", $shown, "   [", $prec, "]   stored ",
    $p->birthdate->format("Y-m-d"), "   api ", $p->partialDateIso("birthdate"), "\n";
echo "           age ", ($p->age ?? "(none)"), "   approximate ",
    ($p->dateIsApproximate("birthdate") ? "yes" : "no"), "\n";

echo "\n  display  ", $shown, ($shown === $payload["expect_display"] ? "  ok" : "  !! WANT ".$payload["expect_display"]), "\n";
echo "  precision ", $prec, ($prec === $payload["expect_precision"] ? "  ok" : "  !! WANT ".$payload["expect_precision"]), "\n";

// Show the reasoning back, so a re-run re-states it rather than hiding it.
$born = $at["date"];
echo "\n  window:  born between ", ($b[0])."-01-19", " and ", ($b[0] + 1)."-01-18",
    " — 347 of 365 days fall in ", $b[0], "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "parsons-birth-year" apply_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 170 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "Expect the profile to read \"c. 2002\" — not \"2002\". The c. is the"
echo "whole point: it is a year derived from an age, not a year anybody"
echo "recorded."
