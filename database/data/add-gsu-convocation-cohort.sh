#!/usr/bin/env bash
#
# Add the other six defendants from the July 29, 2022 Georgia State University
# Convocation Center action (the same case as Laurel Leckert, already in the DB).
# All seven were arrested July 29, 2022, booked into the Fulton County Jail, and
# held about five days until bail was set at $9,000 each (Atlanta Solidarity
# Fund) — likely release on/around August 3, 2022. They were indicted on
# second-degree burglary, criminal damage to property, and misdemeanor
# obstruction, for entering the unfinished Convocation Center and damaging a
# door, walls, and signs while protesting the contractor Brasfield & Gorrie and
# its role in building Cop City.
#
# Outcome (October 24, 2024):
#   - Gina Dickhouse: charges formally dismissed (nolle prosequi).
#   - Abby Walter, Sophia Aria, Gillian Rose Maurer, Melanie Nadine Noyes,
#     Raymond Michael Surya: placed in pretrial diversion, prosecution deferred,
#     charges set to be dismissed on successful completion.
#
# None received a prison sentence; the only confirmed incarceration was the
# ~5-day pretrial detention. Each is tagged into the Cop City cohort
# (ideology "Stop Cop City" + affiliation "Defend the Atlanta Forest").
#
# Idempotent create-or-update, matched by name. Run from the repo root:
#   bash database/data/add-gsu-convocation-cohort.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$intro = "One of seven people arrested July 29, 2022 at the unfinished Georgia State University Convocation Center while protesting the contractor Brasfield & Gorrie and its role in building Cop City. All seven were booked into the Fulton County Jail and held about five days until bail was set at $9,000 each (Atlanta Solidarity Fund), a likely release on or around August 3, 2022. They were indicted on second-degree burglary, criminal damage to property, and misdemeanor obstruction for entering the building and damaging a door, walls, and signs. ";
$dismissedTail = "On October 24, 2024 all charges were formally dismissed.";
$diversionTail = "On October 24, 2024 the case was placed into a pretrial-diversion program, with prosecution deferred and the charges set to be dismissed on successful completion.";

// [name, first, middle, last, aka, outcome]  outcome: dismissed | diversion
$people = [
    ["Gina Dickhouse",       "Gina",    "",       "Dickhouse", "Gina Dickhaus", "dismissed"],
    ["Abby Walter",          "Abby",    "",       "Walter",    "",              "diversion"],
    ["Sophia Aria",          "Sophia",  "",       "Aria",      "",              "diversion"],
    ["Gillian Rose Maurer",  "Gillian", "Rose",   "Maurer",    "",              "diversion"],
    ["Melanie Nadine Noyes", "Melanie", "Nadine", "Noyes",     "",              "diversion"],
    ["Raymond Michael Surya","Raymond", "Michael","Surya",     "",              "diversion"],
];

$inst = \App\Models\Institution::firstOrCreate(
    ["name" => "Fulton County Jail"],
    ["city" => "Atlanta", "state" => "Georgia"]
);

$created = 0; $updated = 0;
foreach ($people as $row) {
    [$name, $first, $middle, $last, $aka, $outcome] = $row;
    $dismissed = ($outcome === "dismissed");
    $desc = $intro . ($dismissed ? $dismissedTail : $diversionTail);
    $convicted = $dismissed
        ? "No — all charges dismissed October 24, 2024"
        : "No — pretrial diversion (October 24, 2024); charges deferred, to be dismissed on completion";

    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("name", $name)->first();
    if (! $p) {
        $p = \App\Models\Prisoner::create([
            "name" => $name, "first_name" => $first, "last_name" => $last,
            "middle_name" => $middle ?: null, "aka" => $aka ?: null,
            "description" => $desc, "state" => "Georgia", "era" => "2020s",
            "ideologies" => ["Stop Cop City"], "affiliation" => ["Defend the Atlanta Forest"],
            "in_custody" => false, "released" => true,
        ]);
        echo "Created {$p->slug}\n"; $created++;
    } else {
        if (empty($p->middle_name) && $middle) { $p->middle_name = $middle; }
        if (empty($p->aka) && $aka) { $p->aka = $aka; }
        $p->ideologies = array_values(array_unique(array_merge((array) $p->ideologies, ["Stop Cop City"])));
        $p->affiliation = array_values(array_unique(array_merge((array) $p->affiliation, ["Defend the Atlanta Forest"])));
        if (empty($p->state)) { $p->state = "Georgia"; }
        $p->in_custody = false; $p->released = true;
        $p->save();
        echo "Updated {$p->slug}\n"; $updated++;
    }

    $c = $p->cases()->first();
    if (! $c) { $c = new \App\Models\PrisonerCase(); $c->prisoner_id = $p->id; }
    $c->institution_id = $inst->id;
    $c->charges = "Second-degree burglary, criminal damage to property, and misdemeanor obstruction (bail set at $9,000)";
    $c->convicted = $convicted;
    $c->setPartialDate("arrest_date", 2022, 7, 29);
    $c->setPartialDate("incarceration_date", 2022, 7, 29);
    $c->setPartialDate("release_date", 2022, 8, 3);
    $c->imprisoned_for_days = 5;
    $c->save();
}

echo "Created {$created}, updated {$updated}.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. GSU Convocation Center cohort added."
