#!/usr/bin/env bash
#
# Add nine U.S. AIDS-activist (ACT UP / Housing Works) incarceration cases,
# from case info supplied by the site owner. Data in
# database/data/act-up-aids-activists.json:
#
#   Terrence McGuckin, Kate Sorensen, Paul Davis (ACT UP/Philadelphia, 2000 RNC)
#   Richard "Doe" Racklin, Jeff Schuerholz (ACT UP/LA, 1992 Houston RNC)
#   Karl Soehnlein, Luis Salazar (ACT UP/NY, 1990 Albany AIDS-budget sit-in)
#   Walter Armstrong (ACT UP/NY, July 1992 NYC AIDS march)
#   Charles King (Housing Works; repeated overnight holds)
#
# All tagged ideology "AIDS Activism". Where a release date was given, custody
# days are set; where the duration is undetermined, no custody duration is
# asserted (no incarceration_date). Idempotent create-or-update by name. Run
# from the repo root:
#   bash database/data/add-act-up-aids-activists.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$people = json_decode(file_get_contents(base_path("database/data/act-up-aids-activists.json")), true);
if (! is_array($people)) { echo "Could not read JSON.\n"; return; }

$setDate = function ($c, $field, $d) {
    if ($d === null) { return; }
    $c->setPartialDate($field, $d[0], $d[1] ?? null, $d[2] ?? null);
};

$created = 0; $updated = 0;
foreach ($people as $x) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("name", $x["name"])->first();
    if (! $p) {
        $p = \App\Models\Prisoner::create([
            "name" => $x["name"], "first_name" => $x["first"], "last_name" => $x["last"],
            "aka" => $x["aka"] ?? null,
            "description" => $x["description"], "state" => $x["state"], "era" => $x["era"],
            "ideologies" => ["AIDS Activism"], "affiliation" => $x["affiliation"],
            "in_custody" => (bool) $x["in_custody"], "released" => (bool) $x["released"],
        ]);
        echo "  created {$p->slug}\n"; $created++;
    } else {
        $p->ideologies = array_values(array_unique(array_merge((array) $p->ideologies, ["AIDS Activism"])));
        $p->affiliation = array_values(array_unique(array_merge((array) $p->affiliation, (array) $x["affiliation"])));
        $p->save();
        echo "  updated {$p->slug}\n"; $updated++;
    }

    $c = $p->cases()->first() ?: (new \App\Models\PrisonerCase(["prisoner_id" => $p->id]));
    $c->prisoner_id = $p->id;
    $c->charges = $x["charges"];
    $c->convicted = $x["convicted"];
    $setDate($c, "arrest_date", $x["arrest"] ?? null);
    $setDate($c, "incarceration_date", $x["incarceration"] ?? null);
    $setDate($c, "release_date", $x["release"] ?? null);
    if (array_key_exists("days", $x) && $x["days"] !== null && ($x["incarceration"] ?? null) !== null && ($x["release"] ?? null) !== null) {
        $c->imprisoned_for_days = (int) $x["days"];
    }
    $c->save();
}

echo "\nCreated {$created}, updated {$updated}.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. ACT UP / AIDS activist cases added."
