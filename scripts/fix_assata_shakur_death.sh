#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/fix_assata_shakur_death.sh
#
# Sets Assata Shakurs death date. She died in Havana, Cuba on
# September 25, 2025, age 78, as confirmed by the Cuban Foreign
# Ministry. She had lived in Cuban political asylum since 1984
# following her 1979 escape from the Clinton Correctional Facility
# for Women in New Jersey, where she was serving a life sentence on
# her 1977 conviction for the May 2, 1973 New Jersey Turnpike shootout
# that killed New Jersey State Trooper Werner Foerster (a conviction
# her supporters and many legal scholars consider unjust). She had
# been on the FBIs Most Wanted Terrorists list with a $2 million
# bounty since 2013.
#
# This script also:
#   - sets in_custody=false (she is deceased)
#   - sets currently_in_exile=false (she died in exile)
#   - leaves in_exile=true (historical fact; she died in exile)
#   - closes any open exile case row by setting end_of_exile to her
#     death date and death_in_custody_date as well
#
# Source: Cuban Foreign Ministry announcement, September 26, 2025;
# Associated Press, September 26, 2025.
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Assata Shakur")
    ->orWhere("name", "like", "%Assata Shakur%")
    ->orWhere("name", "like", "%JoAnne Chesimard%")
    ->orWhere("name", "like", "%Joanne Chesimard%")
    ->first();

if (!$p) {
    echo "ERROR: Assata Shakur not found\n";
    exit(1);
}

$dirty = false;
if (!$p->death_date || $p->death_date->toDateString() !== "2025-09-25") {
    $p->death_date = "2025-09-25";
    $dirty = true;
}
if ($p->in_custody) { $p->in_custody = false; $dirty = true; }
if ($p->currently_in_exile) { $p->currently_in_exile = false; $dirty = true; }
if (!$p->in_exile) { $p->in_exile = true; $dirty = true; }

if ($dirty) {
    $p->save();
    echo "Updated Assata Shakur prisoner record: death_date=2025-09-25, in_custody=false, currently_in_exile=false, in_exile=true.\n";
} else {
    echo "Assata Shakur prisoner record already correct.\n";
}

// Close any open exile/custody case rows by setting end_of_exile
// and death_in_custody_date to her death date.
foreach ($p->cases as $case) {
    $changed = false;
    if ($case->in_exile_since && !$case->end_of_exile) {
        $case->end_of_exile = "2025-09-25";
        $changed = true;
    }
    if (!$case->death_in_custody_date) {
        $case->death_in_custody_date = "2025-09-25";
        $changed = true;
    }
    if ($changed) {
        $case->save();
        echo "  Updated case id={$case->id}: end_of_exile / death_in_custody_date set to 2025-09-25.\n";
    }
}

echo "Assata Shakur death-date fix complete.\n";
'

echo
echo "Assata Shakur death-date update complete."
