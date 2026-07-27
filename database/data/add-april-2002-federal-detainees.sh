#!/usr/bin/env bash
#
# Six activists arrested in the April 12, 2002 protest and held in federal
# custody:
#
#   Scott Anderson, Mark Dombowsky, Hillary Hosta, Jeremy Paster,
#   Scott Paul, Pelle Pettersson
#
#   Protest and arrests   April 12, 2002
#   FBI processing        evening of April 12, 2002
#   Federal custody       April 12 to April 15, 2002 (3 days)
#
# Only what the source states is recorded. No charge text, organisation or
# gender is asserted, because none was given -- guessing any of those would
# put unsourced claims on a public record. Set them in the admin, or send the
# details and this script can be extended.
#
# The records are flagged released and marked minor_case, the three-day
# detention being the same shape as the other short holds behind the "Include
# minor cases" toggle.
#
# Idempotent -- updates rather than duplicating. Run from the repo root:
#   bash database/data/add-april-2002-federal-detainees.sh
# then place the new records:
#   php artisan prisoners:auto-place-zero-sort

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$people = [
    ["Scott Anderson", "Scott", "Anderson"],
    ["Mark Dombowsky", "Mark", "Dombowsky"],
    ["Hillary Hosta", "Hillary", "Hosta"],
    ["Jeremy Paster", "Jeremy", "Paster"],
    ["Scott Paul", "Scott", "Paul"],
    ["Pelle Pettersson", "Pelle", "Pettersson"],
];

$bioFor = function (string $name): string {
    return $name." was arrested in the protest of April 12, 2002, processed by the FBI that evening and held in federal custody until release on April 15, 2002, roughly three days later.";
};

$created = 0; $updated = 0;

foreach ($people as [$name, $first, $last]) {
    $p = Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", [strtolower($name)])->first();

    if (! $p) {
        $p = Prisoner::create([
            "name" => $name, "first_name" => $first, "last_name" => $last,
            "era" => "2000s",
            "in_custody" => false, "released" => true,
            "minor_case" => true,
            "description" => $bioFor($name),
        ]);
        $created++;
        echo "created  {$p->name}  [{$p->slug}]\n";
    } else {
        if (! $p->description) { $p->description = $bioFor($name); }
        if (! $p->era) { $p->era = "2000s"; }
        $updated++;
        echo "updated  {$p->name}  [{$p->slug}]  (already present -- check it is the same person)\n";
    }

    $p->first_name = $first;
    $p->last_name = $last;
    $p->in_custody = false;
    $p->awaiting_trial = false;
    $p->released = true;
    $p->save();

    $c = $p->cases()->where("charges", "like", "%April 12, 2002%")->first();
    if (! $c) {
        $c = $p->cases()->create([
            "charges" => "Federal custody following arrest in the protest of April 12, 2002.",
        ]);
    }
    $c->convicted = "Held in federal custody; no outcome recorded in the available source";
    $c->sentence = "Arrested April 12, 2002 and processed by the FBI that evening. Held in federal custody until release on April 15, 2002 -- roughly three days.";
    $c->setPartialDate("arrest_date", 2002, 4, 12);
    $c->setPartialDate("incarceration_date", 2002, 4, 12);
    $c->setPartialDate("release_date", 2002, 4, 15);
    $c->save();

    echo "    case 2002-04-12 -> 2002-04-15, days=".($c->imprisoned_for_days ?? "null")." (expected 3)\n";
    if ($p->sort_order == 0) {
        echo "    sort_order 0 -- no affiliation recorded, so auto-place has nothing to cluster on\n";
    }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone. Created {$created}, updated {$updated}.\n";
'

echo
echo "Done."
