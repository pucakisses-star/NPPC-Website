#!/usr/bin/env bash
#
# Custody dates for the "Know Your Enemy" pamphlet figures (site owner's
# research), scoped to the 1951 New York Smith Act roundup and prosecutions,
# plus Israel Amter's separate 1930 case. Sidney Steinberg is handled elsewhere
# (Twain Harte) and is not touched here.
#
# The 13 non-fugitives were arrested June 20, 1951; brief pretrial custody ended
# on bail by June 29, 1951 (kept as arrest_date, NOT continuous incarceration).
# Convicted defendants began serving about January 11, 1955. Dates are recorded
# at the confidence the sources support: exact days where documented, month or
# year precision where partial, and release left BLANK (bound stated in the note)
# where only a floor is known. imprisoned_for_days then derives from the dates.
#
#   Exact prison release: Jones (Oct 23, 1955), Jerome (May 17, 1957),
#     Flynn (May 25, 1957), Bittelman (May 26, 1957); Amter 1930 (Oct 21, 1930).
#   Bounded/undocumented release left blank: Mindel, Fred Fine, James Jackson.
#   Acquitted, pretrial-only: Simon Gerson, Isidore Begun.
#
# Idempotent (dates are set; the custody note is appended once). Run from the
# repo root:
#   bash database/data/set-know-your-enemy-custody-dates.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$bail = "Held briefly after the June 20, 1951 arrest (released on bail by June 29, 1951); ";

$people = [
  ["slugs"=>["alexander-trachtenberg"],"names"=>["Alexander Trachtenberg"],
   "arrest"=>[1951,6,20],"inc"=>[1955,1,11],"rel"=>[1955,4,22],"conv"=>null,
   "note"=>$bail."imprisoned from about January 11, 1955 and freed on or shortly after April 22, 1955 when a new trial was granted. Exact prison discharge day not found."],
  ["slugs"=>["v-j-jerome"],"names"=>["V. J. Jerome"],
   "arrest"=>[1951,6,20],"inc"=>[1955,1,11],"rel"=>[1957,5,17],"conv"=>null,
   "note"=>$bail."served the Smith Act sentence from about January 11, 1955 to May 17, 1957."],
  ["slugs"=>["simon-w-gerson","simon-gerson"],"names"=>["Simon W. Gerson","Simon Gerson"],
   "arrest"=>[1951,6,20],"inc"=>[1951,6,20],"rel"=>null,"conv"=>"No — acquitted by directed verdict, September 24, 1952",
   "note"=>"Brief pretrial custody after the June 20, 1951 arrest; released on bail by June 29, 1951 and acquitted by directed verdict on September 24, 1952. No sentence served."],
  ["slugs"=>["elizabeth-gurley-flynn"],"names"=>["Elizabeth Gurley Flynn"],
   "arrest"=>[1951,6,20],"inc"=>[1955,1,11],"rel"=>[1957,5,25],"conv"=>null,
   "note"=>$bail."served her Smith Act sentence from about January 11, 1955 to May 25, 1957 at the Alderson Federal Reformatory."],
  ["slugs"=>["alexander-bittelman"],"names"=>["Alexander Bittelman"],
   "arrest"=>[1951,6,20],"inc"=>[1955,1,11],"rel"=>[1957,5,26],"conv"=>null,
   "note"=>$bail."served from about January 11, 1955 to May 26, 1957."],
  ["slugs"=>["betty-gannett"],"names"=>["Betty Gannett"],
   "arrest"=>[1951,6,20],"inc"=>[1955,1,11],"rel"=>[1956,9,null],"conv"=>null,
   "note"=>$bail."imprisoned from about January 11, 1955 and released about September 1956 (roughly two years served; exact day not found)."],
  ["slugs"=>["isidore-begun","isadore-begun"],"names"=>["Isidore Begun","Isadore Begun"],
   "arrest"=>[1951,6,20],"inc"=>[1951,6,20],"rel"=>null,"conv"=>"No — acquitted by directed verdict, September 24, 1952",
   "note"=>"Brief pretrial custody after the June 20, 1951 arrest; released on bail by June 29, 1951 and acquitted by directed verdict on September 24, 1952. No sentence served."],
  ["slugs"=>["jacob-mindel"],"names"=>["Jacob Mindel"],
   "arrest"=>[1951,6,20],"inc"=>[1955,1,11],"rel"=>null,"conv"=>null,
   "note"=>$bail."imprisoned from about January 11, 1955 and still held through at least June 11, 1956. Exact discharge date not documented."],
  ["slugs"=>["claudia-jones"],"names"=>["Claudia Jones"],
   "arrest"=>[1951,6,20],"inc"=>[1955,1,11],"rel"=>[1955,10,23],"conv"=>null,
   "note"=>$bail."served from about January 11 to October 23, 1955 at the Alderson Federal Reformatory, then was deported."],
  ["slugs"=>["israel-amter"],"names"=>["Israel Amter"],
   "arrest"=>[1930,3,6],"inc"=>[1930,4,21],"rel"=>[1930,10,21],"conv"=>"Yes — 1930 unemployment-demonstration case (his 1951 Smith Act case was severed for ill health)",
   "note"=>"Served about six months, roughly April 21 to October 21, 1930, for the March 6, 1930 unemployment demonstration. His 1951 New York Smith Act case was severed for ill health, so he served no Smith Act prison term (released on bail by June 29, 1951)."],
  ["slugs"=>["william-weinstone","william-w-weinstone"],"names"=>["William Weinstone","William W. Weinstone","William Wolf Weinstone"],
   "arrest"=>[1951,6,20],"inc"=>[1955,1,11],"rel"=>[1957,null,null],"conv"=>null,
   "note"=>$bail."imprisoned from about January 11, 1955 and released in 1957 (about two years served; exact day not found)."],
  ["slugs"=>["george-blake-charney","george-charney"],"names"=>["George Blake Charney","George Charney"],
   "arrest"=>[1951,6,20],"inc"=>[1955,1,11],"rel"=>[1955,4,22],"conv"=>null,
   "note"=>$bail."imprisoned from about January 11, 1955 and freed on or shortly after April 22, 1955 when a new trial was granted. Exact discharge day not found."],
  ["slugs"=>["fred-fine","fred-m-fine"],"names"=>["Fred Fine","Fred M. Fine","Frederick Fine"],
   "arrest"=>[1955,11,null],"inc"=>null,"rel"=>null,"conv"=>"Convicted in the later Smith Act trial; reversed 1958",
   "note"=>"A fugitive from the 1951 indictment, Fine surrendered about November 1955 (some sources say early 1956) and was released on bail; no post-conviction prison term is documented before his conviction was reversed in 1958. Exact custody dates not established."],
  ["slugs"=>["louis-weinstock"],"names"=>["Louis Weinstock"],
   "arrest"=>[1951,6,20],"inc"=>[1955,1,11],"rel"=>[1957,5,null],"conv"=>null,
   "note"=>$bail."imprisoned from about January 11, 1955 and released about May 1957 (exact day not found)."],
  ["slugs"=>["james-edward-jackson","james-e-jackson"],"names"=>["James Edward Jackson","James E. Jackson"],
   "arrest"=>[1955,12,2],"inc"=>[1955,12,2],"rel"=>null,"conv"=>"Surrendered 1955; 1951 Smith Act conviction reversed 1958",
   "note"=>"A fugitive from the 1951 indictment, Jackson surrendered December 2, 1955 and was released on \$20,000 bail; no post-conviction Smith Act prison term is documented before the conviction was reversed in 1958. A separate 1962 contempt case is not included here."],
];

$sp = function ($c, $field, $d) {
    if ($d === null) { $c->setPartialDate($field, null); }
    else { $c->setPartialDate($field, $d[0], $d[1] ?? null, $d[2] ?? null); }
};

$done = 0; $missing = [];
foreach ($people as $x) {
    $p = null;
    foreach ($x["slugs"] as $s) { $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $s)->first(); if ($p) break; }
    if (! $p) { foreach ($x["names"] as $n) { $p = \App\Models\Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", [mb_strtolower($n)])->first(); if ($p) break; } }
    if (! $p) { $missing[] = $x["names"][0]; continue; }

    $c = $p->cases()->first();
    if (! $c) { $c = new \App\Models\PrisonerCase(); $c->prisoner_id = $p->id; }

    $sp($c, "arrest_date", $x["arrest"]);
    $sp($c, "incarceration_date", $x["inc"]);
    $sp($c, "release_date", $x["rel"]);
    if ($x["conv"] !== null) { $c->convicted = $x["conv"]; }

    $cur = (string) $c->sentence;
    if (strpos($cur, $x["note"]) === false) { $c->sentence = $cur === "" ? $x["note"] : ($cur." — ".$x["note"]); }
    $c->save();

    $days = $c->imprisoned_for_days === null ? "n/a" : $c->imprisoned_for_days;
    echo str_pad($p->slug, 26)." arr ".str_pad((string)($c->partialDateIso("arrest_date") ?? "-"),10)
        ." inc ".str_pad((string)($c->partialDateIso("incarceration_date") ?? "-"),10)
        ." rel ".str_pad((string)($c->partialDateIso("release_date") ?? "(blank)"),10)." days={$days}\n";
    $done++;
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nUpdated {$done} record(s).\n";
if ($missing) { echo "Not found: ".implode(", ", $missing)." — give me the exact site slug and I will map it.\n"; }
'

echo
echo "Done. Know Your Enemy custody dates set."
