#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/fix_ice_detention_incarceration_dates.sh
#
# For ICE / immigration detention cases (no separate booking step
# between arrest and detention - civil arrestees go straight from
# CBP/ICE custody into ICE detention), incarceration_date should equal
# arrest_date. Several 2025 Palestine-solidarity wave case rows have
# arrest_date and release_date set but no incarceration_date, so the
# booted hook leaves imprisoned_for_days null and the page-level Time
# Imprisoned counter does not render.
#
# This script targets the 2025 wave by name and back-fills
# incarceration_date = arrest_date on any of their case rows that have
# arrest_date but no incarceration_date. The booted hook then computes
# imprisoned_for_days correctly on save.
set -e

php artisan tinker --execute='
$names = [
    "Mahmoud Khalil",
    "Rumeysa Ozturk",
    "Mohsen Mahdawi",
    "Badar Khan Suri",
    "Mohammed Hoque",
    "Aditya Wahyu Harsono",
    "Alireza Doroudi",
    "Leqaa Kordia",
    "Yunseo Chung",
    "Rasha Alawieh",
];

$fixed = 0;
foreach ($names as $name) {
    $p = App\Models\Prisoner::where("name", $name)->first();
    if (!$p) {
        echo "skip: prisoner {$name} not found\n";
        continue;
    }
    foreach ($p->cases as $case) {
        if ($case->arrest_date && ! $case->incarceration_date) {
            $oldInc = $case->incarceration_date?->toDateString() ?? "(null)";
            $case->incarceration_date = $case->arrest_date;
            $case->save();
            $fixed++;
            echo "fixed: {$name} (case id={$case->id}) incarceration_date {$oldInc} -> {$case->incarceration_date->toDateString()}; imprisoned_for_days now = {$case->imprisoned_for_days}\n";
        } else {
            echo "ok:    {$name} (case id={$case->id}) - incarceration_date already set or no arrest_date\n";
        }
    }
}
echo "\nDone. {$fixed} case rows updated.\n";
'

echo
echo "ICE-detention incarceration-date fix complete."
