#!/usr/bin/env bash
#
# HUAC contempt-prisoner sentence data (July 2026). Follow-up to the
# add-huac-jafrc-gaps.sh audit: fills per-person sentence and custody
# detail for the 34 HUAC contempt prisoners from the documented tally —
# notably the November 13, 1950 separate imprisonment of Helen Bryan (at
# Alderson) and Ernestina Fleischman, Barsky's six months (released after
# about five), the Hollywood Ten sentences, and the Klan leaders' terms.
#
# Case rows are matched by contempt-related charge text (falling back to
# the record's only case); records with multiple cases and no identifiable
# contempt case (e.g. Eugene Dennis, whose Smith Act case is separate) are
# skipped rather than risk writing onto the wrong case. Fill-if-empty
# throughout; idempotent.
#
# Run from the repo root:  bash database/data/enrich-huac-sentences.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$find = fn (string $slug) => \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();

// Find the HUAC-contempt case: by charge text, else the sole case.
$contemptCase = function ($p) {
    if (! $p) { return null; }
    $case = $p->cases()
        ->where(function ($q) {
            $q->where("charges", "like", "%ontempt%")
              ->orWhere("charges", "like", "%Un-American%")
              ->orWhere("charges", "like", "%HUAC%");
        })->first();
    if ($case) { return $case; }
    return $p->cases()->count() === 1 ? $p->cases()->first() : null;
};

$fill = function ($p, array $fields) use ($contemptCase): void {
    $case = $contemptCase($p);
    if (! $case) { if ($p) { echo "SKIP {$p->slug} (no identifiable contempt case)\n"; } return; }
    $changed = false;
    foreach ($fields as $f => $v) {
        if (! empty($case->{$f})) { continue; }
        $case->{$f} = $v;
        $changed = true;
    }
    if ($changed) { $case->save(); echo "CASE {$p->slug}\n"; }
};

// --- JAFRC -----------------------------------------------------------------
$fill($find("edward-k-barsky"), [
    "incarceration_date" => "1950-06-07",
    "sentence" => "Six months and a $500 fine; released after about five months",
    "imprisoned_for_days" => 150,
]);
$fill($find("lyman-r-bradley"), [
    "incarceration_date" => "1950-06-07",
    "sentence" => "Three months and a $500 fine",
    "imprisoned_for_days" => 90,
]);
$fill($find("howard-fast"), [
    "incarceration_date" => "1950-06-07",
    "sentence" => "Three months and a $500 fine",
    "imprisoned_for_days" => 90,
]);
$p = $find("helen-bryan");
$fill($p, [
    "incarceration_date" => "1950-11-13",
    "sentence" => "Three months and a $500 fine",
    "imprisoned_for_days" => 90,
]);
if ($p) {
    $case = $p->cases()->first();
    if ($case && empty($case->institution_id)) {
        $alderson = \App\Models\Institution::where("name", "like", "%Alderson%")->first()
            ?? \App\Models\Institution::create(["name" => "Federal Prison Camp, Alderson", "city" => "Alderson", "state" => "West Virginia"]);
        $case->institution_id = $alderson->id;
        $case->save();
        echo "CASE helen-bryan institution\n";
    }
}
$fill($find("ernestina-fleischman"), [
    "incarceration_date" => "1950-11-13",
    "sentence" => "Three months and a $500 fine",
    "imprisoned_for_days" => 90,
]);

// --- Hollywood Ten ---------------------------------------------------------
foreach ([
    "alvah-bessie"      => ["One year and a $1,000 fine; served about ten months", 300],
    "herbert-biberman"  => ["Six months and a $500 fine", 180],
    "lester-cole"       => ["One year and a $1,000 fine", 300],
    "edward-dmytryk"    => ["Six months and a $500 fine; served about four and a half months", 135],
    "ring-lardner-jr"   => ["One year and a $1,000 fine; served nearly ten months", 295],
    "john-howard-lawson" => ["One year and a $1,000 fine", 300],
    "albert-maltz"      => ["One year and a $1,000 fine", 300],
    "samuel-ornitz"     => ["One year and a $1,000 fine", 300],
    "adrian-scott"      => ["One year and a $1,000 fine", 300],
    "dalton-trumbo"     => ["One year and a $1,000 fine; served about ten months", 300],
] as $slug => [$sentence, $days]) {
    $fill($find($slug), ["sentence" => $sentence, "imprisoned_for_days" => $days]);
}

// --- Other contempt cases --------------------------------------------------
$fill($find("leon-josephson"), ["sentence" => "One year", "imprisoned_for_days" => 365]);
$fill($find("lloyd-barenblatt"), ["sentence" => "Six months", "imprisoned_for_days" => 180]);
$fill($find("chandler-davis"), ["sentence" => "Six months", "imprisoned_for_days" => 180]);
$fill($find("arthur-mcphaul"), ["sentence" => "Nine months and a $500 fine"]);
$fill($find("frank-wilkinson"), ["sentence" => "One year; served nine months in 1961", "imprisoned_for_days" => 270]);
$fill($find("carl-braden"), ["sentence" => "One year; served nine months in 1961", "imprisoned_for_days" => 270]);

// --- Klan leaders ----------------------------------------------------------
foreach (["robert-shelton", "j-robert-jones", "robert-scoggin"] as $slug) {
    $fill($find($slug), ["sentence" => "One year and a $1,000 fine; served with good-time reduction"]);
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. HUAC sentence data applied."
