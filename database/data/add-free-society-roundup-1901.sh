#!/usr/bin/env bash
#
# The September 1901 Chicago "Free Society" roundup connected to Abraham Isaak
# (arrests after President McKinley was shot). Records custody for the other
# named arrestees the site owner verified. Updates the records that already
# exist (Mary Isaak, Hippolyte Havel), adds a dedicated 1901 Chicago case to
# Emma Goldman without touching her other cases, and creates the nine missing
# records.
#
#   Held without bail to Sept 23, 1901 (~17 days): Abraham Isaak Jr., Clemens
#     Pfuetzner, Alfred Schneider, Hippolyte Havel, Enrico (Henry) Travaglio,
#     Jay Fox (court name Morris J. Fox), Martin Rasnick, Michael Roz.
#   Case dismissed by Sept 10, 1901 (held at least overnight; exact release
#     uncertain): Mary/Maria Isaak, Marie Isaak (the ~16-year-old daughter),
#     Julia Mechanic.
#   Emma Goldman: arrested Sept 10, released Sept 24, 1901 (~14 days).
#
# Idempotent: existing records are matched by slug/name and updated; new records
# are created once and updated on re-runs.
#
# Run from the repo root:
#   bash database/data/add-free-society-roundup-1901.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$cook = \App\Models\Institution::firstOrCreate(["name"=>"Cook County Jail"], ["city"=>"Chicago","state"=>"Illinois"]);
$police = \App\Models\Institution::firstOrCreate(["name"=>"Chicago police custody"], ["city"=>"Chicago","state"=>"Illinois"]);
$charges = "Detained in the post-assassination anarchist roundup (Chicago, September 1901)";

$men = "Arrested in the September 6-7, 1901 Chicago roundup after President McKinley was shot; held without bail at the Cook County Jail until September 23, 1901 (about 17 days), then released without prosecution.";
$women = "Detained in the September 1901 Chicago roundup after President McKinley was shot; case dismissed by September 10, 1901 (held at least overnight; exact release uncertain).";

// slug, [name candidates], name, first, last, gender, aka, bio(create-only), inst, conv, inc, rel, note
$people = [
  ["mary-isaak", ["Mary Isaak","Maria Isaak"], "Mary Isaak","Mary","Isaak","Female",null,null,
   "police","No — case dismissed",[1901,9,6],[1901,9,10],$women],
  ["marie-isaak", ["Marie Isaak"], "Marie Isaak","Marie","Isaak","Female","Mary Isaak",
   "Marie (Mary) Isaak was the roughly sixteen-year-old daughter of the anarchist publishers Abraham and Mary Isaak. She was detained at a Chicago police station in the September 1901 roundup that followed the shooting of President McKinley; her case was dismissed by September 10, 1901.",
   "police","No — case dismissed",[1901,9,6],[1901,9,10],$women],
  ["julia-mechanic", ["Julia Mechanic"], "Julia Mechanic","Julia","Mechanic","Female",null,
   "Julia Mechanic was a Russian immigrant seamstress living with the Isaak family in Chicago. She was detained with the Isaak women in the September 1901 anarchist roundup after the shooting of President McKinley; her case was dismissed by September 10, 1901.",
   "police","No — case dismissed",[1901,9,6],[1901,9,10],$women],
  ["abraham-isaak-jr", ["Abraham Isaak Jr.","Abraham Isaak Jr","Abe Isaak Jr."], "Abraham Isaak Jr.","Abraham","Isaak","Male","Abe Isaak Jr.",
   "Abraham (Abe) Isaak Jr. was the son of the anarchist publisher Abraham Isaak and a contributor to the family paper Free Society. He was arrested in the September 6-7, 1901 Chicago roundup after the shooting of President McKinley and held without bail at the Cook County Jail until September 23, 1901, when he was released without prosecution.",
   "cook","No — released without prosecution",[1901,9,6],[1901,9,23],$men],
  ["clemens-pfuetzner", ["Clemens Pfuetzner","Clement Pfuetzner"], "Clemens Pfuetzner","Clemens","Pfuetzner","Male",null,
   "Clemens Pfuetzner (also printed Clement, Clemence, or Clarence Pfuetzner) was among the Chicago anarchists arrested in the September 6-7, 1901 roundup after the shooting of President McKinley and held without bail at the Cook County Jail until September 23, 1901, when he was released without prosecution.",
   "cook","No — released without prosecution",[1901,9,6],[1901,9,23],$men],
  ["alfred-schneider", ["Alfred Schneider"], "Alfred Schneider","Alfred","Schneider","Male",null,
   "Alfred Schneider was among the Chicago anarchists arrested in the September 6-7, 1901 roundup after the shooting of President McKinley and held without bail at the Cook County Jail until September 23, 1901, when he was released without prosecution.",
   "cook","No — released without prosecution",[1901,9,6],[1901,9,23],$men],
  ["hippolyte-havel", ["Hippolyte Havel"], "Hippolyte Havel","Hippolyte","Havel","Male",null,null,
   "cook","No — released without prosecution",[1901,9,6],[1901,9,23],$men],
  ["enrico-travaglio", ["Enrico Travaglio","Henry Travaglio"], "Enrico Travaglio","Enrico","Travaglio","Male","Henry Travaglio",
   "Enrico Travaglio (called Henry Travaglio in English-language newspapers) worked as a printer or typesetter for the anarchist paper Free Society. He was arrested in the September 6-7, 1901 Chicago roundup after the shooting of President McKinley and held without bail at the Cook County Jail until September 23, 1901.",
   "cook","No — released without prosecution",[1901,9,6],[1901,9,23],$men],
  ["jay-fox", ["Jay Fox","Morris J. Fox","Morris Fox"], "Jay Fox","Jay","Fox","Male","Morris J. Fox",
   "Jay Fox was an anarchist labor journalist; contemporary court reports rendered his name as Morris J. Fox. He was among those arrested in the September 6-7, 1901 Chicago roundup after the shooting of President McKinley and held without bail at the Cook County Jail until September 23, 1901.",
   "cook","No — released without prosecution",[1901,9,6],[1901,9,23],$men],
  ["martin-rasnick", ["Martin Rasnick","Martin Rosenick","Martin Raznick"], "Martin Rasnick","Martin","Rasnick","Male",null,
   "Martin Rasnick (also printed Rosenick, Raznick, or Razner), a cloakmaker, was among the Chicago anarchists arrested in the September 6-7, 1901 roundup after the shooting of President McKinley and held without bail at the Cook County Jail until September 23, 1901.",
   "cook","No — released without prosecution",[1901,9,6],[1901,9,23],$men],
  ["michael-roz", ["Michael Roz","Michael Rose","Michael Ross"], "Michael Roz","Michael","Roz","Male",null,
   "Michael Roz (variously printed Rose, Roze, Raz, or Ross) was among those arrested in the September 6-7, 1901 Chicago roundup after the shooting of President McKinley and held without bail at the Cook County Jail until September 23, 1901.",
   "cook","No — released without prosecution",[1901,9,6],[1901,9,23],$men],
];

$created = 0; $updated = 0;
foreach ($people as $x) {
    [$slug,$cands,$name,$first,$last,$gender,$aka,$bio,$inst,$conv,$inc,$rel,$note] = $x;
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug",$slug)->first();
    if (! $p) { foreach ($cands as $n) { $p = \App\Models\Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", [mb_strtolower($n)])->first(); if ($p) break; } }
    $creating = ! $p;
    if ($creating) {
        $p = new \App\Models\Prisoner();
        $p->name = $name; $p->first_name = $first; $p->last_name = $last;
        if ($gender) { $p->gender = $gender; }
        if ($aka) { $p->aka = $aka; }
        if ($bio) { $p->description = $bio; }
        $p->state = "Illinois";
        $p->ideologies = ["Anarchism"];
        $p->affiliation = ["Anarchism"];
    }
    $p->in_custody = false; $p->released = true;
    $p->save();

    $instId = $inst === "cook" ? $cook->id : ($inst === "police" ? $police->id : null);
    $c = $p->cases()->first();
    if (! $c) { $c = new \App\Models\PrisonerCase(); $c->prisoner_id = $p->id; }
    if (empty($c->charges)) { $c->charges = $charges; }
    if ($instId) { $c->institution_id = $instId; }
    $c->convicted = $conv;
    $c->setPartialDate("arrest_date", $inc[0], $inc[1], $inc[2]);
    $c->setPartialDate("incarceration_date", $inc[0], $inc[1], $inc[2]);
    $c->setPartialDate("release_date", $rel[0], $rel[1], $rel[2]);
    $cur = (string) $c->sentence;
    if (strpos($cur, $note) === false) { $c->sentence = $cur === "" ? $note : ($cur." — ".$note); }
    $c->save();

    echo ($creating ? "CREATED " : "updated ").str_pad($p->slug,20)
        ." ".$c->partialDateIso("incarceration_date")." -> ".$c->partialDateIso("release_date")
        ." ({$c->imprisoned_for_days} days)\n";
    $creating ? $created++ : $updated++;
}

// --- Emma Goldman: add a dedicated 1901 Chicago case (leave her others alone) ---
$eg = \App\Models\Prisoner::withoutGlobalScopes()->where("slug","emma-goldman")->first();
if ($eg) {
    $dup = $eg->cases()->whereDate("arrest_date","1901-09-10")->first();
    if (! $dup) {
        $c = new \App\Models\PrisonerCase();
        $c->prisoner_id = $eg->id;
        $c->institution_id = $cook->id;
        $c->charges = "Detained on suspicion of conspiracy in the McKinley assassination (Chicago, 1901)";
        $c->convicted = "No — released without prosecution (no evidence)";
        $c->sentence = "Arrested in Chicago on September 10, 1901 on suspicion of involvement in the alleged McKinley assassination conspiracy; held without bail and released September 24, 1901 (about 14 days) after authorities admitted they had no evidence.";
        $c->setPartialDate("arrest_date",1901,9,10);
        $c->setPartialDate("incarceration_date",1901,9,10);
        $c->setPartialDate("release_date",1901,9,24);
        $c->save();
        echo "Emma Goldman: added 1901 Chicago roundup case ({$c->imprisoned_for_days} days)\n";
    } else {
        echo "Emma Goldman: 1901 Chicago case already present — left alone.\n";
    }
} else { echo "Emma Goldman not found.\n"; }

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nCreated {$created}, updated {$updated} (plus Emma Goldman).\nDone.\n";
'

echo
echo "Done. Free Society 1901 roundup custody recorded."
