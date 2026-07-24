#!/usr/bin/env bash
#
# Custody dates for five De Mau Mau defendants (site owner's research). All were
# taken into custody in the mid-October 1972 arrests. Three died in custody
# (recorded via death_in_custody_date, from which the model derives the release
# date); two were paroled decades later.
#
#   Donald Taylor    Oct 12, 1972 -> died in prison in 1991 (year only)   ~18-19 yrs
#   Edward Moran Jr. Oct 12, 1972 -> killed in Lake County Jail, Jun 13, 1973
#                    (database record was misnamed "Howard Moran" — corrected)
#   Michael Clark    Oct 12, 1972 -> paroled Jul 3, 2019                   46y 8m 21d
#   Nathaniel Burse  by Oct 15, 1972 -> killed in Lake County Jail, Jun 13, 1973
#                    (exact incarceration day within the mid-Oct arrests unconfirmed)
#   Reuben Taylor    Oct 12, 1972 -> paroled Apr 27, 2018                  45y 6m 15d
#
# Idempotent (dates/flags set; custody note appended once). Run from the repo root:
#   bash database/data/set-de-mau-mau-custody-dates.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$people = [
  ["slug"=>"donald-taylor","rename"=>null,
   "inc"=>[1972,10,12],"death"=>[1991,null,null],"rel"=>null,"released"=>false,"pdeath"=>[1991,null,null],
   "note"=>"Incarcerated October 12, 1972; died in prison in 1991 (exact date not found), after about 18-19 years. No release."],
  ["slug"=>"howard-moran","rename"=>["Edward Moran Jr.","Edward","Moran"],
   "inc"=>[1972,10,12],"death"=>[1973,6,13],"rel"=>null,"released"=>false,"pdeath"=>[1973,6,13],
   "note"=>"Incarcerated October 12, 1972; killed in the Lake County Jail on June 13, 1973 (8 months, 1 day). The database record was misnamed Howard Moran; corrected to Edward Moran Jr."],
  ["slug"=>"michael-clark","rename"=>null,
   "inc"=>[1972,10,12],"death"=>null,"rel"=>[2019,7,3],"released"=>true,"pdeath"=>null,
   "note"=>"Incarcerated October 12, 1972; paroled July 3, 2019 (46 years, 8 months, 21 days)."],
  ["slug"=>"nathaniel-burse","rename"=>null,
   "inc"=>[1972,10,15],"death"=>[1973,6,13],"rel"=>null,"released"=>false,"pdeath"=>[1973,6,13],
   "note"=>"Incarcerated by October 15, 1972 (exact day within the mid-October 1972 arrests unconfirmed); killed in the Lake County Jail on June 13, 1973 (about 8 months)."],
  ["slug"=>"reuben-taylor","rename"=>null,
   "inc"=>[1972,10,12],"death"=>null,"rel"=>[2018,4,27],"released"=>true,"pdeath"=>null,
   "note"=>"Incarcerated October 12, 1972; paroled April 27, 2018 (45 years, 6 months, 15 days). Often spelled Ruben Taylor in Illinois records."],
];

$sp = function ($m, $field, $d) {
    if ($d === null) { $m->setPartialDate($field, null); }
    else { $m->setPartialDate($field, $d[0], $d[1] ?? null, $d[2] ?? null); }
};

$done = 0; $missing = [];
foreach ($people as $x) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $x["slug"])->first();
    if (! $p) { $missing[] = $x["slug"]; continue; }

    if ($x["rename"] !== null) {
        $p->name = $x["rename"][0];
        $p->first_name = $x["rename"][1];
        $p->last_name = $x["rename"][2];
    }
    $p->in_custody = false;
    $p->released = $x["released"];
    if ($x["pdeath"] !== null) { $sp($p, "death_date", $x["pdeath"]); }
    $p->save();

    $c = $p->cases()->first();
    if (! $c) { $c = new \App\Models\PrisonerCase(); $c->prisoner_id = $p->id; }

    $sp($c, "incarceration_date", $x["inc"]);
    if ($x["death"] !== null) {
        $sp($c, "death_in_custody_date", $x["death"]);   // model derives release_date from this
    } elseif ($x["rel"] !== null) {
        $c->setPartialDate("death_in_custody_date", null);
        $sp($c, "release_date", $x["rel"]);
    }

    $cur = (string) $c->sentence;
    if (strpos($cur, $x["note"]) === false) { $c->sentence = $cur === "" ? $x["note"] : ($cur." — ".$x["note"]); }
    $c->save();

    $days = $c->imprisoned_for_days === null ? "n/a" : $c->imprisoned_for_days;
    echo str_pad($p->slug, 18)." ".str_pad($p->name, 18)
        ." inc ".str_pad((string)($c->partialDateIso("incarceration_date") ?? "-"), 10)
        ." out ".str_pad((string)($c->partialDateIso("release_date") ?? "-"), 10)." days={$days}\n";
    $done++;
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nUpdated {$done} record(s).\n";
if ($missing) { echo "Not found: ".implode(", ", $missing)."\n"; }
'

echo
echo "Done. De Mau Mau custody dates set."
