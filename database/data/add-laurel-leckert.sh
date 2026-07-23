#!/usr/bin/env bash
#
# Add / update Laurel Amary Leckert, one of the seven Stop Cop City protesters
# arrested July 29, 2022 in the first wave of forest-defense arrests and held in
# the Fulton County Jail for five days until bail was set at $9,000 each
# (per the Atlanta Solidarity Fund). All charges were dismissed Oct 24, 2024.
#
#   Arrested / incarcerated : July 29, 2022
#   Released (approx.)      : August 3, 2022  (5 days)
#   Bail                    : $9,000
#   Charges                 : Second-degree burglary, criminal damage to
#                             property, and misdemeanor obstruction
#   Outcome                 : Charges dismissed October 24, 2024
#
# There is no dedicated bail/outcome column, so bail is folded into the charges
# text and the dismissal is recorded in the case "convicted" field. She is
# tagged into the Cop City cohort (ideology "Stop Cop City" + affiliation
# "Defend the Atlanta Forest") so the chronological sort groups her with it.
#
# Idempotent: matched by name (any record whose name contains "Leckert"). If she
# already exists the case is updated in place; otherwise the record is created.
# Run from the repo root:
#   bash database/data/add-laurel-leckert.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$name = "Laurel Amary Leckert";

$p = \App\Models\Prisoner::withoutGlobalScopes()
    ->where("name", $name)
    ->orWhere("name", "like", "%Leckert%")
    ->first();

if (! $p) {
    $p = \App\Models\Prisoner::create([
        "name"        => $name,
        "first_name"  => "Laurel",
        "middle_name" => "Amary",
        "last_name"   => "Leckert",
        "description" => "One of seven Stop Cop City protesters arrested July 29, 2022 in the first wave of arrests defending the Atlanta forest. The seven were held in the Fulton County Jail for five days until bail was set at $9,000 each (Atlanta Solidarity Fund). She was charged with second-degree burglary, criminal damage to property, and misdemeanor obstruction; all charges were dismissed October 24, 2024.",
        "state"       => "Georgia",
        "era"         => "2020s",
        "ideologies"  => ["Stop Cop City"],
        "affiliation" => ["Defend the Atlanta Forest"],
        "in_custody"  => false,
        "released"    => true,
    ]);
    echo "Created prisoner {$p->slug}.\n";
} else {
    $ideol = array_values(array_unique(array_merge((array) $p->ideologies, ["Stop Cop City"])));
    $aff = array_values(array_unique(array_merge((array) $p->affiliation, ["Defend the Atlanta Forest"])));
    $p->ideologies = $ideol;
    $p->affiliation = $aff;
    if (empty($p->middle_name)) { $p->middle_name = "Amary"; }
    if (empty($p->state)) { $p->state = "Georgia"; }
    $p->in_custody = false;
    $p->released = true;
    $p->save();
    echo "Updated existing prisoner {$p->slug}.\n";
}

// Fulton County Jail institution (matched or created).
$inst = \App\Models\Institution::firstOrCreate(
    ["name" => "Fulton County Jail"],
    ["city" => "Atlanta", "state" => "Georgia"]
);

$c = $p->cases()->first();
if (! $c) { $c = new \App\Models\PrisonerCase(); $c->prisoner_id = $p->id; }
$c->institution_id = $inst->id;
$c->charges = "Second-degree burglary, criminal damage to property, and misdemeanor obstruction (bail set at $9,000)";
$c->convicted = "No — all charges dismissed October 24, 2024";
$c->setPartialDate("arrest_date", 2022, 7, 29);
$c->setPartialDate("incarceration_date", 2022, 7, 29);
$c->setPartialDate("release_date", 2022, 8, 3);
$c->imprisoned_for_days = 5;
$c->save();
echo "Case set: 5 days (2022-07-29 to ~2022-08-03), charges dismissed 2024-10-24.\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Laurel Amary Leckert added/updated."
