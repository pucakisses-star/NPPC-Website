#!/usr/bin/env bash
#
# Sedition Act (1798-1801) prisoner enrichment (July 2026). All eight people
# confined in the American sedition prosecutions were already in the
# database; this fills the structured case data:
#
#  - Case rows created for the three records that had none: Charles Holt
#    (federal Sedition Act), David Frothingham (New York common-law
#    seditious libel), William Durrell (federal common-law sedition;
#    released after about two weeks by a partial pardon from Adams).
#  - Sentences filled on the existing cases: Matthew Lyon (four months +
#    $1,000), Thomas Cooper (six months + $400), James Thompson Callender
#    (nine months + $200), and Anthony Haswell (two months + $200 with the
#    July 9, 1800 release, per Vermont History vol. 78).
#  - Callender gains the standard-spelling alias "James Thomson Callender".
#
# Deliberately NOT added: Victor Collot and William Cobbett — deportation
# orders were prepared under the Alien Friends Act but never executed, both
# left the country on their own, and neither was confined.
#
# Fill-if-empty throughout; idempotent.
#
# Run from the repo root:  bash database/data/enrich-sedition-act.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$find = fn (string $slug) => \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
$fillCase = function ($p, array $fill): void {
    if (! $p) { return; }
    $case = $p->cases()->first();
    if (! $case) { return; }
    $changed = false;
    foreach ($fill as $f => $v) {
        if (! empty($case->{$f})) { continue; }
        $case->{$f} = $v;
        $changed = true;
    }
    if ($changed) { $case->save(); echo "CASE {$p->slug}\n"; }
};
$createCase = function ($p, array $fields, array $precision = []): void {
    if (! $p || $p->cases()->count() > 0) { return; }
    $case = $p->cases()->create($fields);
    if ($precision) { $case->date_precision = $precision; $case->save(); }
    echo "CASE created {$p->slug}\n";
};

// --- Case rows for the three records that had none -------------------------
$createCase($find("charles-holt"), [
    "charges" => "Sedition Act of 1798 — for anti-militarist articles in his New London Bee",
    "convicted" => "Yes",
    "sentence" => "Three months and a $200 fine",
    "incarceration_date" => "1800-01-01",
    "imprisoned_for_days" => 90,
], ["incarceration_date" => "year"]);

$createCase($find("david-frothingham"), [
    "charges" => "Seditious libel under New York common law — for reprinting an item in the Argus charging Alexander Hamilton with scheming to buy and suppress the paper",
    "convicted" => "Yes",
    "sentence" => "Four months in the Bridewell and a $100 fine",
    "incarceration_date" => "1799-01-01",
    "imprisoned_for_days" => 120,
], ["incarceration_date" => "year"]);

$createCase($find("william-durrell"), [
    "charges" => "Federal common-law sedition — over a reprinted item in his Mount Pleasant Register critical of President Adams",
    "convicted" => "Yes",
    "sentence" => "Four months and a fine; released after about two weeks by a partial pardon from President Adams",
    "incarceration_date" => "1800-01-01",
    "imprisoned_for_days" => 14,
], ["incarceration_date" => "year"]);

// --- Sentence data on the existing cases -----------------------------------
$fillCase($find("matthew-lyon"), [
    "sentence" => "Four months and a $1,000 fine",
    "incarceration_date" => "1798-10-09",
    "release_date" => "1799-02-09",
    "imprisoned_for_days" => 123,
]);
$fillCase($find("thomas-cooper"), [
    "sentence" => "Six months and a $400 fine",
    "imprisoned_for_days" => 180,
]);
$fillCase($find("james-thompson-callender"), [
    "sentence" => "Nine months and a $200 fine",
    "imprisoned_for_days" => 270,
]);
$fillCase($find("anthony-haswell"), [
    "sentence" => "Two months and a $200 fine",
    "release_date" => "1800-07-09",
    "imprisoned_for_days" => 60,
]);

// --- Callender standard-spelling alias -------------------------------------
$p = $find("james-thompson-callender");
if ($p && empty($p->aka)) {
    $p->aka = "James Thomson Callender";
    $p->save();
    echo "AKA james-thompson-callender\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Sedition Act enrichments applied."
