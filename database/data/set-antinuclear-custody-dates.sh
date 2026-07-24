#!/usr/bin/env bash
#
# Custody dates for antinuclear / peace-movement prisoners (site owner's data).
#
# Golden Rule crew — FIXES a database error: George Willoughby, Orion Sherwood
# and William Huntington were shown as if their June 1958 incarceration never
# ended. Each actually had TWO 1958 detentions, so their cases are replaced with:
#   1) May 1-7, 1958   six days awaiting judgment; the 60-day term was suspended
#                      and they were released on probation
#   2) June 4 - August 3, 1958   the 60-day contempt sentence served in Honolulu
#
# Single detentions:
#   Marjorie Swann   Aug 10, 1959 - Jan 11, 1960   (Omaha Action, Mead NE missile base)
#   Edward Sanders   Aug 8-24, 1961                (Polaris Action, New London CT)
#   Eugene Keyes     Aug 8-24, 1961                (Polaris Action; end reconstructed
#                                                    from the documented 17-day term)
#
# Idempotent (cases are replaced each run). Run from the repo root:
#   bash database/data/set-antinuclear-custody-dates.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$honolulu = \App\Models\Institution::firstOrCreate(["name"=>"Honolulu Jail"], ["city"=>"Honolulu","state"=>"Hawaii"]);

// --- Golden Rule crew: two 1958 detentions each (replace existing cases) ---
foreach (["george-willoughby","orion-sherwood","william-huntington"] as $slug) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug",$slug)->first();
    if (! $p) { echo "MISS {$slug}\n"; continue; }
    $p->in_custody = false; $p->released = true; $p->save();
    $p->cases()->delete();

    $c1 = new \App\Models\PrisonerCase();
    $c1->prisoner_id = $p->id; $c1->institution_id = $honolulu->id;
    $c1->charges = "First attempt to sail the ketch Golden Rule toward the U.S. Pacific nuclear-test zone (Honolulu)";
    $c1->convicted = "Contempt of court; 60-day sentence suspended";
    $c1->judge = "Jon Wiig";
    $c1->sentence = "Held six days awaiting judgment (May 1-7, 1958); the 60-day term was suspended and he was released on probation.";
    $c1->setPartialDate("arrest_date",1958,5,1);
    $c1->setPartialDate("incarceration_date",1958,5,1);
    $c1->setPartialDate("release_date",1958,5,7);
    $c1->save();

    $c2 = new \App\Models\PrisonerCase();
    $c2->prisoner_id = $p->id; $c2->institution_id = $honolulu->id;
    $c2->charges = "Second attempt to sail the Golden Rule into the U.S. Pacific nuclear-test zone";
    $c2->convicted = "Yes — criminal contempt";
    $c2->judge = "Jon Wiig";
    $c2->sentence = "Served the 60-day contempt sentence in the Honolulu Jail (June 4 - August 3, 1958).";
    $c2->setPartialDate("arrest_date",1958,6,4);
    $c2->setPartialDate("incarceration_date",1958,6,4);
    $c2->setPartialDate("release_date",1958,8,3);
    $c2->save();

    echo "{$slug}: 2 cases (May 1-7 [{$c1->imprisoned_for_days}d]; Jun 4-Aug 3 [{$c2->imprisoned_for_days}d])\n";
}

// --- Single-detention peace activists (replace with one case) --------------
$singles = [
  ["slug"=>"marjorie-swann","inc"=>[1959,8,10],"rel"=>[1960,1,11],
   "charges"=>"Trespass at the Mead, Nebraska intercontinental-missile base (Omaha Action)","conv"=>"Yes",
   "note"=>"Omaha Action: jailed August 10, 1959 and released January 11, 1960."],
  ["slug"=>"edward-sanders","inc"=>[1961,8,8],"rel"=>[1961,8,24],
   "charges"=>"Polaris Action protest against Polaris nuclear-missile submarines (New London area, Connecticut)","conv"=>"Yes",
   "note"=>"Polaris Action: jailed August 8-24, 1961."],
  ["slug"=>"eugene-keyes","inc"=>[1961,8,8],"rel"=>[1961,8,24],
   "charges"=>"Polaris Action protest against Polaris nuclear-missile submarines (New London area, Connecticut)","conv"=>"Yes",
   "note"=>"Polaris Action: jailed about August 8-24, 1961 (17 days; the end date is reconstructed from the documented 17-day term)."],
];
foreach ($singles as $x) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug",$x["slug"])->first();
    if (! $p) { echo "MISS {$x['slug']}\n"; continue; }
    $p->in_custody = false; $p->released = true; $p->save();
    $p->cases()->delete();
    $c = new \App\Models\PrisonerCase();
    $c->prisoner_id = $p->id;
    $c->charges = $x["charges"]; $c->convicted = $x["conv"]; $c->sentence = $x["note"];
    $c->setPartialDate("arrest_date",$x["inc"][0],$x["inc"][1],$x["inc"][2]);
    $c->setPartialDate("incarceration_date",$x["inc"][0],$x["inc"][1],$x["inc"][2]);
    $c->setPartialDate("release_date",$x["rel"][0],$x["rel"][1],$x["rel"][2]);
    $c->save();
    echo "{$x['slug']}: inc ".$c->partialDateIso("incarceration_date")." -> rel ".$c->partialDateIso("release_date")." ({$c->imprisoned_for_days} days)\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Antinuclear / peace-movement custody dates set."
