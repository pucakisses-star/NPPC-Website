#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/update_existing_from_tpp_2.sh
#
# Backfill pass for the second TPP batch (rows 101-200). For
# prisoners already in the database that were skipped with
# EXISTS: at runtime, fills in missing fields on the canonical
# case row from the supplied TPP data. Never overwrites existing
# values - only writes when null/empty.
set -e

php artisan tinker --execute='
$rows = [
    ["name"=>"Justin Solondz","sentenced_date"=>"2011-04-22","sentence_months"=>84,"charges"=>"Federal conspiracy to commit arson, arson of a government building, arson of property used in interstate commerce, use and carrying of a destructive device, and making unregistered destructive devices - Operation Backfire"],
    ["name"=>"Justin Franchi Solondz","sentenced_date"=>"2011-04-22","sentence_months"=>84,"charges"=>"Federal conspiracy to commit arson, arson of a government building, arson of property used in interstate commerce, use and carrying of a destructive device, and making unregistered destructive devices - Operation Backfire"],
    ["name"=>"Nicole Kissane","sentenced_date"=>"2017-02-16","sentence_months"=>21,"charges"=>"18 USC 43 conspiracy to violate the Animal Enterprise Terrorism Act and substantive AETA violations [3 counts] - 2013 multi-state ALF-claimed actions including mink-farm releases, fur-store vandalism, and a Montana bobcat release"],
    ["name"=>"Joseph Buddenberg","sentenced_date"=>"2017-02-16","sentence_months"=>24,"charges"=>"18 USC 43 conspiracy to violate the Animal Enterprise Terrorism Act - 2013 multi-state ALF-claimed actions including mink-farm releases and fur-store vandalism"],
    ["name"=>"Kevin Olliff","sentenced_date"=>"2014-07-08","sentence_months"=>36,"charges"=>"18 USC 43 force, violence, and threats involving animal enterprises (AETA) [2 counts] - 2013 Morris IL mink-farm release of approximately 2,000 mink"],
    ["name"=>"Ruby Montoya","sentenced_date"=>"2022-09-22","sentence_months"=>72,"charges"=>"18 USC 2 conspiracy to damage an energy facility; 18 USC 844 use of fire in the commission of a felony [4 counts]; 28 USC 1366 malicious use of fire [4 counts] - 2016-2017 Dakota Access Pipeline arson and torch attacks across Iowa and South Dakota"],
];

$updated = 0; $skipped = 0; $missing = 0;
foreach ($rows as $row) {
    $name = $row["name"];
    $p = App\Models\Prisoner::where("name", $name)
        ->orWhere("name", "like", "%" . $name . "%")
        ->first();
    if (!$p) {
        echo "MISSING: {$name}\n";
        $missing++;
        continue;
    }
    $case = $p->cases()->orderByRaw("incarceration_date IS NULL, incarceration_date DESC")->first();
    if (!$case) {
        echo "NO_CASE: {$name}\n";
        $skipped++;
        continue;
    }
    $changes = [];
    if (!empty($row["sentenced_date"]) && empty($case->sentenced_date)) {
        $case->sentenced_date = $row["sentenced_date"];
        $changes[] = "sentenced_date";
    }
    if (!empty($row["charges"]) && empty($case->charges)) {
        $case->charges = $row["charges"];
        $changes[] = "charges";
    }
    if (!empty($row["sentence_months"]) && empty($case->sentence)) {
        $case->sentence = $row["sentence_months"] . " months federal prison";
        $changes[] = "sentence";
    }
    if (!empty($changes)) {
        $case->save();
        echo "UPDATED ({$name}): " . implode(", ", $changes) . "\n";
        $updated++;
    } else {
        $skipped++;
    }
}
echo "\nSummary: updated={$updated}, no-change={$skipped}, missing={$missing}\n";
'

echo
echo "TPP batch 2 backfill complete."
