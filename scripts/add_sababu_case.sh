#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_sababu_case.sh
#
# Adds the missing case row to Kojo Bomani Sababu (Grailing Brown),
# Black Liberation Army member held continuously in federal custody
# from 1975 until his October 5, 2021 release on parole. Sababu was
# convicted in 1976 in connection with a 1975 attempted armed bank
# robbery in Trenton, New Jersey that resulted in the death of
# patrolman John Schroeder; he received a federal life sentence. In
# 1988 he received an additional 15 years for a federal RICO/escape
# conspiracy charged jointly with Puerto Rican independence leader
# Oscar Lopez Rivera (planned helicopter escape from USP Leavenworth).
# After 46 years in federal custody he was released to a Newark NJ
# halfway house on October 5, 2021.
#
# Source: AddSpecificPrisonerCases command data; Federal Bureau of
# Prisons inmate locator; Jericho Movement.
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Kojo Bomani Sababu (Grailing Brown)")
    ->orWhere("name", "like", "%Kojo Bomani Sababu%")
    ->orWhere("name", "like", "%Grailing Brown%")
    ->first();

if (!$p) {
    echo "ERROR: Kojo Bomani Sababu (Grailing Brown) not found\n";
    exit(1);
}

if ($p->cases()->exists()) {
    echo "Sababu already has cases (count={$p->cases()->count()}); skipping.\n";
    exit(0);
}

$inst = App\Models\Institution::firstOrCreate(
    ["name" => "Federal Bureau of Prisons (location varied)"]
);

App\Models\PrisonerCase::create([
    "prisoner_id"        => $p->id,
    "institution_id"     => $inst->id,
    "charges"            => "Federal armed bank robbery and felony murder of Trenton, New Jersey patrolman John Schroeder in a 1975 Black Liberation Army expropriation action intended to fund revolutionary work. Sababu was convicted in 1976 and sentenced to life in federal prison. In 1988 he received an additional 15 years for a federal RICO/escape conspiracy charged jointly with Puerto Rican independence leader Oscar Lopez Rivera (planned helicopter escape from USP Leavenworth).",
    "arrest_date"        => "1975-08-01",
    "incarceration_date" => "1975-08-01",
    "sentenced_date"     => "1977-06-15",
    "release_date"       => "2021-10-05",
    "convicted"          => "Yes - federal jury verdict, 1976 (BLA bank-robbery / felony-murder case); subsequent 1988 federal RICO/escape-conspiracy conviction.",
    "sentence"           => "Life in federal prison + 15 additional years (1988 RICO conviction). Held continuously in federal custody from August 1975 (locations included USP Leavenworth, ADX Florence, FCI Allenwood) until paroled to a Newark, New Jersey halfway house on October 5, 2021 after 46 years.",
]);

// Make sure prisoner-level flags reflect his release
$dirty = false;
if ($p->in_custody) { $p->in_custody = false; $dirty = true; }
if (!$p->released) { $p->released = true; $dirty = true; }
if ($dirty) {
    $p->save();
    echo "Updated prisoner-level flags: in_custody=false, released=true.\n";
}

echo "Inserted Kojo Bomani Sababu case (1975-2021, federal life, paroled October 5 2021).\n";
'

echo
echo "Kojo Bomani Sababu case add complete."
