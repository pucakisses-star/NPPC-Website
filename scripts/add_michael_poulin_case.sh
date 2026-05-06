#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_michael_poulin_case.sh
#
# Adds a case row to Michael D. Poulin (full name Michael Devlyn
# Poulin)s existing prisoner record. The AddNuclearResisterPrisoners
# command had bio data but never created the case row.
#
# Action: In spring 2003, Poulin (then 62) loosened bolts on a
# Bonneville Power Administration high-voltage transmission tower
# near U.S. Highway 97 and the Columbia Plywood plant south of
# Klamath Falls, Oregon, and made a similar attempt at a tower in
# Anderson, California. Both were detected before any electrical
# interruption. Federal charges followed under the energy-facility
# destruction statute.
#
# Sentence: convicted in 2003, served approximately 27 months at
# the Federal Prison Camp at Sheridan, Oregon. BOP register number
# 14793-097.
#
# Note: precise arrest, sentencing, and release dates were not
# located in publicly searchable sources; the dates below are
# approximations consistent with the published "27 months at FPC
# Sheridan" figure. Update if you find a primary source.
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Michael D. Poulin")
    ->orWhere("name", "like", "%Michael%Poulin%")
    ->first();

if (!$p) {
    echo "ERROR: Michael D. Poulin not found\n";
    exit(1);
}
echo "Found prisoner: {$p->name} (id={$p->id})\n";

if ($p->cases()->exists()) {
    echo "Michael D. Poulin already has cases (count={$p->cases()->count()}); skipping insert.\n";
    exit(0);
}

$inst = App\Models\Institution::firstOrCreate(
    ["name" => "Federal Prison Camp, Sheridan"],
    ["city" => "Sheridan", "state" => "Oregon"]
);

App\Models\PrisonerCase::create([
    "prisoner_id"        => $p->id,
    "institution_id"     => $inst->id,
    "charges"            => "Federal destruction of an energy facility - 2003 attempts to loosen bolts on Bonneville Power Administration high-voltage transmission towers near U.S. Highway 97 south of Klamath Falls, Oregon and at a tower in Anderson, California. Both attempts were detected before any electrical interruption. Poulin and supporters described the action as a symbolic protest against the fragility of U.S. empire.",
    "arrest_date"        => "2003-04-01",
    "incarceration_date" => "2003-09-01",
    "release_date"       => "2005-12-01",
    "sentence"           => "27 months federal prison at the Federal Prison Camp, Sheridan, Oregon. BOP register number 14793-097. (Exact arrest, sentencing, and release dates not located in publicly searchable sources; dates above are approximations consistent with the published 27-month figure.)",
    "convicted"          => "Yes - 2003 federal conviction",
]);

if (!$p->inmate_number) {
    $p->inmate_number = "14793-097";
    $p->save();
    echo "Set inmate_number=14793-097.\n";
}

echo "Inserted Michael D. Poulin case (~27 months FPC Sheridan).\n";
'

echo
echo "Michael D. Poulin case add complete."
