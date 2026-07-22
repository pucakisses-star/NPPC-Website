#!/usr/bin/env bash
#
# Add the 15 "Stop Cop City" RICO defendants missing from the database.
#
# A coverage check against the 61 defendants named in the September 5, 2023
# Fulton County RICO indictment found that 46 were already in the database
# (plus the three Atlanta Solidarity Fund bail-fund organizers, the domestic-
# terrorism arrestees, and Manuel "Tortuguita" Terán). These 15 named RICO
# defendants — each booked into the Fulton County Jail and released on bond —
# were missing. Roster in database/data/cop-city-rico-missing.json.
#
# They match the site's existing Cop City records: ideologies Stop Cop City /
# Police Accountability / Anarchism / Environmental Activism, era 2020s,
# released. Pronouns are unknown, so bios use they/them; gender is left unset.
#
# Idempotent: a prisoner is created only if no record with that name exists.
# Run from the repo root:
#   bash database/data/add-cop-city-rico-missing.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$rows = json_decode(file_get_contents(base_path("database/data/cop-city-rico-missing.json")), true);
if (! is_array($rows)) { echo "Could not read roster JSON\n"; return; }

$ideologies = ["Stop Cop City", "Police Accountability", "Anarchism", "Environmental Activism"];
$created = 0; $skipped = 0;

foreach ($rows as $r) {
    if (\App\Models\Prisoner::withoutGlobalScopes()->where("name", $r["name"])->exists()) {
        echo "  skip {$r["name"]} (already present)\n"; $skipped++; continue;
    }

    $num = $r["rico"] ?? null;
    $ord = $num ? "defendant #{$num} of the 61 people" : "one of the 61 people";
    $dt  = ! empty($r["dt"])
        ? " They were also among those arrested in the March 5, 2023 mass arrest at the South River Forest and charged with domestic terrorism."
        : "";
    $desc = "{$r["name"]} is {$ord} indicted on September 5, 2023 under the Georgia RICO Act (O.C.G.A. 16-14-4) in the Fulton County prosecution of the \"Stop Cop City\" / Defend the Atlanta Forest movement opposing the Atlanta Public Safety Training Center. A warrant was issued for their arrest; they were booked into the Fulton County Jail and released on bond.{$dt} The sweeping indictment, condemned by civil-liberties organizations as an attack on protected protest, remains pending.";

    $data = array_filter([
        "name" => $r["name"],
        "first_name" => $r["first_name"] ?? null,
        "middle_name" => $r["middle_name"] ?? null,
        "last_name" => $r["last_name"] ?? null,
        "description" => $desc,
        "state" => $r["state"] ?? null,
        "era" => "2020s",
    ], fn ($v) => $v !== null);
    $data["ideologies"] = $ideologies;
    $data["released"] = true;
    $data["in_custody"] = false;
    $data["awaiting_trial"] = true;

    $p = \App\Models\Prisoner::create($data);

    $c = new \App\Models\PrisonerCase();
    $c->prisoner_id = $p->id;
    $inst = \App\Models\Institution::firstOrCreate(["name" => "Fulton County Jail"], ["city" => "Atlanta", "state" => "Georgia"]);
    $c->institution_id = $inst->id;
    $c->charges = "Violation of the Georgia RICO Act (O.C.G.A. 16-14-4) — Stop Cop City / Defend the Atlanta Forest prosecution";
    $c->convicted = "No — case pending (awaiting trial)";
    $c->sentence = "Named in the September 5, 2023 Fulton County RICO indictment; booked into the Fulton County Jail and released on bond. Case pending.";
    $c->setPartialDate("arrest_date", 2023, 9);
    $c->save();

    echo "  created {$p->name} (slug {$p->slug})\n";
    $created++;
}

echo "Created {$created}, skipped {$skipped}.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Missing Stop Cop City RICO defendants added."
