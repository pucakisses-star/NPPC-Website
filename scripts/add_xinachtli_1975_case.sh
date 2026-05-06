#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_xinachtli_1975_case.sh
#
# Adds Xinachtli (Alvaro Luna Hernandez) first imprisonment as a
# second case row alongside his existing 1996 Alpine TX disarming
# case.
#
# History: In September 1975 Hernandez was arrested in connection
# with the murder of motel clerk Robert Anthony Beard in Alpine /
# West Texas. The prosecution case rested almost entirely on the
# testimony of Palmira Hernandez (no relation), a former motel
# employee under arrest on unrelated drug charges who was granted
# full immunity in exchange for her testimony; she later recanted
# and stated that police had threatened her with prosecution unless
# she identified Alvaro as the killer. He was convicted in 1976,
# narrowly escaped a death sentence, and received what was in
# effect a life term in Texas state prison. After journalist Paul
# Harasim documented the prosecutorial misconduct, the conviction
# was undone and Hernandez was released in March 1991, after more
# than 15 years in custody.
#
# This is the case row for that 1975-1991 incarceration; he is
# already in the database with a separate case for his July 18,
# 1996 Alpine police-officer disarming and 50-year TDCJ sentence.
#
# Sources: The Ballad of Alvaro Luna Hernandez, CounterPunch (2011);
# Denver Anarchist Black Cross prisoner profile; Wikipedia.
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Alvaro Luna Hernandez")
    ->orWhere("name", "like", "%Alvaro Luna Hernandez%")
    ->orWhere("name", "like", "%Alvaro%Hernandez%")
    ->orWhere("aka", "like", "%Xinachtli%")
    ->first();

if (!$p) {
    echo "ERROR: Xinachtli / Alvaro Luna Hernandez not found\n";
    exit(1);
}
echo "Found prisoner: {$p->name} (id={$p->id})\n";

// Skip if a 1970s case already exists.
$existing = $p->cases()->whereYear("incarceration_date", "<=", 1980)->first();
if ($existing) {
    echo "Pre-1980 case already exists (id={$existing->id}); skipping.\n";
    exit(0);
}

$inst = App\Models\Institution::firstOrCreate(
    ["name" => "Texas Department of Criminal Justice"],
    ["state" => "Texas"]
);

App\Models\PrisonerCase::create([
    "prisoner_id"        => $p->id,
    "institution_id"     => $inst->id,
    "charges"            => "Texas state first-degree murder of motel clerk Robert Anthony Beard. The prosecution case rested almost entirely on the testimony of Palmira Hernandez (no relation), a former motel employee under arrest on unrelated drug charges who was granted full immunity in exchange for identifying Alvaro Hernandez as the killer. No money was taken from the motel and no physical evidence connected Hernandez to the crime. Palmira Hernandez later recanted and stated that police had threatened her with prosecution unless she testified against him. Pulitzer-finalist reporting by Paul Harasim documented systemic prosecutorial misconduct in the case, leading to the convictions reversal and Hernandezs March 1991 release.",
    "arrest_date"        => "1975-09-01",
    "incarceration_date" => "1976-01-01",
    "release_date"       => "1991-03-15",
    "sentence"           => "Effective life sentence in Texas state prison; narrowly escaped a death sentence. Held in TDCJ custody for over 15 years from January 1976 to March 1991, when the conviction was undone after journalist Paul Harasims reporting documented prosecutorial misconduct.",
    "convicted"          => "Yes - 1976 Texas state jury verdict; conviction subsequently undone after the cooperating witness recanted and prosecutorial misconduct was documented",
]);

echo "Inserted Xinachtli 1975-1991 first-imprisonment case (~15 years TDCJ).\n";
'

echo
echo "Xinachtli 1975-1991 case add complete."
