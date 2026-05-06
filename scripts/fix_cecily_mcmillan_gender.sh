#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/fix_cecily_mcmillan_gender.sh
#
# Sets Cecily McMillans gender to Female. The Airtable-imported
# record had it set to Male (or empty).
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Cecily McMillan")->first();
if (!$p) {
    echo "ERROR: Cecily McMillan not found\n";
    exit(1);
}
if ($p->gender !== "Female") {
    $p->gender = "Female";
    $p->save();
    echo "Updated Cecily McMillan gender to Female.\n";
} else {
    echo "Already Female; no change.\n";
}
'

echo
echo "Cecily McMillan gender fix complete."
