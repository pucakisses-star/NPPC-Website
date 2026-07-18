#!/usr/bin/env bash
#
# Labor Defender October 1931 audit (July 2026). Coverage was near-total:
# the Imperial Valley seven, Woodlawn three, Pat Devine, all nine Scottsboro
# defendants, the three Seattle immigration detainees (Glaser, Sakasagsky,
# Wolck), Leo Thompson, Jessie Wakefield, the Centralia four, Mooney,
# Billings, both LA Times bombing prisoners (J. B. McNamara, Matthew
# Schmidt), Sam Bonita, Cornelison, and the whole 30-record Harlan/Evarts
# cluster (incl. W. B. Jones, A. L. Benson, Asa Cusick, Wm. Hudson) are all
# already in the database, as are Paul Crouch, Taft Holmes and William
# Duncan.
#
#  1. Adds the one missing person: Anna Rasefsky (printed "Rasefske"),
#     Stella's sister, jailed for picketing in the 1931-32 Pennsylvania
#     coal strikes.
#  2. Stella Rasefsky gains the printed-spelling alias and the sisters'
#     sentence detail (18 months and two years, unassigned between them).
#  3. Pat Devine's case gains the Atlanta penitentiary, his ~one year
#     served, and the December 25, 1931 deportation to Scotland.
#  4. The Woodlawn three (Muselin, Resetar, Zima) gain their five-year
#     sentences and outcomes: Resetar's October 1931 death in the Blawnox
#     workhouse after Pennsylvania refused hospital release, and the
#     December 1931 pardons of Muselin and Zima.
#
# The six roster entries the review deems unclassifiable (Allen, Holmes,
# Lynch, Madson, Merriee, Pesco) are deliberately NOT added.
#
# Idempotent throughout.
#
# Run from the repo root:  bash database/data/enrich-labor-defender-oct1931.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{"name":"Anna Rasefsky","first_name":"Anna","last_name":"Rasefsky","aka":"Anna Rasefske","description":"Anna Rasefsky (printed \"Rasefske\" in the movement press) was a miner'"'"'s daughter jailed with her sister Stella for picketing during the National Miners'"'"' Union coal strikes that swept Pennsylvania, Ohio and West Virginia in 1931-32. A 1932 Labor Research Association pamphlet records that the sisters received sentences of 18 months and two years, without stating which sister received which term.","state":"Pennsylvania","race":"White","gender":"Female","ideologies":["Labor organizing"],"affiliation":["National Miners Union"],"era":"1930s","released":true,"cases":[{"charges":"Picketing during the 1931-32 Pennsylvania coal strikes","convicted":"Yes","sentence":"18 months or two years (the source records the sisters two sentences without assigning them)"}]}' || true

php artisan tinker --execute='
$find = fn (string $slug) => \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
$fillCase = function ($p, array $fill): void {
    if (! $p || $p->cases()->count() !== 1) { if ($p) { echo "SKIP {$p->slug}\n"; } return; }
    $case = $p->cases()->first();
    $changed = false;
    foreach ($fill as $f => $v) {
        if (! empty($case->{$f})) { continue; }
        $case->{$f} = $v;
        $changed = true;
    }
    if ($changed) { $case->save(); echo "CASE {$p->slug}\n"; }
};

// --- Stella Rasefsky: printed-spelling alias + the sisters sentences ------
$p = $find("stella-rasefsky");
if ($p && empty($p->aka)) { $p->aka = "Stella Rasefske"; $p->save(); echo "AKA stella-rasefsky\n"; }
$fillCase($p, [
    "convicted" => "Yes",
    "sentence" => "18 months or two years (the source records the sisters two sentences without assigning them)",
]);

// --- Pat Devine: Atlanta penitentiary, ~1 year served, deported Dec 1931 --
$p = $find("pat-devine");
if ($p) {
    $case = $p->cases()->first();
    if ($case && empty($case->institution_id)) {
        $atl = \App\Models\Institution::where("name", "like", "%Atlanta%enitentiary%")->first()
            ?? \App\Models\Institution::create(["name" => "United States Penitentiary, Atlanta", "city" => "Atlanta", "state" => "Georgia"]);
        $case->institution_id = $atl->id;
        $case->save();
        echo "CASE pat-devine institution\n";
    }
    $fillCase($p, [
        "sentence" => "Originally 15 years; served about one year in the Atlanta Federal Penitentiary",
        "release_date" => "1931-12-25",
        "convicted" => "Yes — deported to Scotland on release (December 25, 1931)",
    ]);
}

// --- Woodlawn three: five-year sentences and outcomes ---------------------
$fillCase($find("pete-muselin"), [
    "sentence" => "Five years",
    "convicted" => "Yes — pardoned December 1931",
]);
$fillCase($find("tom-zima"), [
    "sentence" => "Five years",
    "convicted" => "Yes — pardoned December 1931",
]);
$p = $find("milan-resetar");
$fillCase($p, [
    "sentence" => "Five years",
    "convicted" => "Yes — died in custody October 1931 after Pennsylvania refused hospital release",
    "death_in_custody_date" => "1931-10-01",
]);
if ($p) {
    $case = $p->cases()->first();
    if ($case && $case->death_in_custody_date && $case->formatPartialDate("death_in_custody_date") !== "October 1931") {
        $case->date_precision = array_merge($case->date_precision ?? [], ["death_in_custody_date" => "month"]);
        $case->save();
        echo "CASE milan-resetar death precision = month\n";
    }
    if (empty($p->death_date)) {
        $p->death_date = "1931-10-01";
        $p->date_precision = array_merge($p->date_precision ?? [], ["death_date" => "month"]);
        $p->save();
        echo "SET milan-resetar death_date = October 1931\n";
    }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Labor Defender October 1931 enrichments applied."
