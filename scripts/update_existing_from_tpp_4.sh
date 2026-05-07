#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/update_existing_from_tpp_4.sh
#
# Catches up on backfills missed by the earlier batch-1, batch-2,
# batch-3 update scripts. Pulls in meaningful TPP fields
# (sentenced_date, sentence text in months, additional charges
# narrative) for existing prisoners where the field is currently
# null/empty. NEVER overwrites existing values.
set -e

php artisan tinker --execute='
$rows = [
    // ---- TPP rows 200-300 ----
    ["name"=>"Tsutomu Shirosaki","sentenced_date"=>"1996-09-12","sentence_months"=>360,"charges"=>"Federal assault with intent to murder, attempted murder of individuals on embassy grounds, willfully and maliciously attempting to harm a U.S. embassy, and committing a violent attack against internationally protected U.S. government personnel - May 14, 1986 mortar attack on U.S. Embassy in Jakarta, Indonesia"],
    ["name"=>"Sara Jane Olson","sentenced_date"=>"2002-01-19","sentence_months"=>168,"charges"=>"California state second-degree murder; possessing explosives with intent to murder [2 counts] - 1975 SLA-related Los Angeles bombing case, paroled after 7 years served"],
    ["name"=>"Kathleen Soliah","sentenced_date"=>"2002-01-19","sentence_months"=>168,"charges"=>"California state second-degree murder; possessing explosives with intent to murder [2 counts] - 1975 SLA-related Los Angeles bombing case, paroled after 7 years served"],
    ["name"=>"Simon Trinidad","sentenced_date"=>"2008-01-28","sentence_months"=>720,"charges"=>"18 USC 1203 hostage-taking and conspiracy to commit hostage-taking [3 counts]; 18 USC 2339A providing material support to terrorists - Colombia kidnapping of three U.S. citizens (Marc Gonsalves, Keith Stansell, Thomas Howes)"],
    ["name"=>"Juvenal Ovidio Ricardo Palmera Pineda","sentenced_date"=>"2008-01-28","sentence_months"=>720,"charges"=>"18 USC 1203 hostage-taking conspiracy and substantive hostage-taking; 18 USC 2339A material support - Colombia FARC kidnapping of three Americans"],
    ["name"=>"Jessica Reznicek","sentenced_date"=>"2021-06-30","sentence_months"=>96,"charges"=>"18 USC 1366(a) conspiracy to damage an energy facility; 18 USC 844(h) use of fire in commission of a felony [4 counts]; 28 USC 1366 malicious use of fire [4 counts] - 2016-2017 Dakota Access Pipeline arson and torch attacks. Sentencing terrorism enhancement (which doubled the prison time from 4 to 8 years) was deemed unreasonable in a June 2022 federal appeal."],
    ["name"=>"Jessica Rae Reznicek","sentenced_date"=>"2021-06-30","sentence_months"=>96,"charges"=>"18 USC 1366(a) conspiracy to damage an energy facility; 18 USC 844(h) use of fire in commission of a felony [4 counts]; 28 USC 1366 malicious use of fire [4 counts] - 2016-2017 Dakota Access Pipeline. Terrorism enhancement deemed unreasonable in June 2022 appeal."],

    // ---- TPP rows 400 - NATO 3 ----
    ["name"=>"Brian Church","sentenced_date"=>"2014-04-25","sentence_months"=>60,"charges"=>"Illinois state possession of an incendiary device with intent to commit arson; possession of an incendiary device with knowledge that another intended to commit arson - 2012 NATO 3 plot in Cook County"],
    ["name"=>"Brent Betterly","sentenced_date"=>"2014-04-25","sentence_months"=>72,"charges"=>"Illinois state possession of an incendiary device with intent to commit arson; possession of an incendiary device with knowledge that another intended to commit arson - 2012 NATO 3 plot in Cook County"],
    ["name"=>"Jared Chase","sentenced_date"=>"2014-04-25","sentence_months"=>96,"charges"=>"Illinois state possession of an incendiary device with intent to commit arson; possession of an incendiary device with knowledge that another intended to commit arson - 2012 NATO 3 plot in Cook County"],

    // ---- TPP rows 388-392 - Cleveland 5 / May Day Bombers ----
    ["name"=>"Connor Stevens","sentenced_date"=>"2012-11-20","sentence_months"=>97,"charges"=>"18 USC 2332a use of weapons of mass destruction [2 counts]; 18 USC 844(h) use of explosive materials and aiding and abetting - 2012 Cleveland 5 May Day bridge-bombing plot in Cuyahoga Valley National Park"],
    ["name"=>"Connor C. Stevens","sentenced_date"=>"2012-11-20","sentence_months"=>97,"charges"=>"18 USC 2332a use of weapons of mass destruction [2 counts]; 18 USC 844(h) use of explosive materials - 2012 Cleveland 5 May Day plot"],
    ["name"=>"Brandon Baxter","sentenced_date"=>"2012-11-20","sentence_months"=>117,"charges"=>"18 USC 2332a use of weapons of mass destruction [2 counts]; 18 USC 844(h) use of explosive materials - 2012 Cleveland 5 May Day bridge-bombing plot"],
    ["name"=>"Joshua Stafford","sentenced_date"=>"2013-11-20","sentence_months"=>120,"charges"=>"18 USC 2332a use of weapons of mass destruction [2 counts]; 18 USC 844(h) use of explosive materials - 2012 Cleveland 5 May Day bridge-bombing plot"],
    ["name"=>"Douglas Wright","sentenced_date"=>"2012-11-20","sentence_months"=>138,"charges"=>"18 USC 2332a use of weapons of mass destruction [2 counts]; 18 USC 844(h) use of explosive materials - 2012 Cleveland 5 May Day bridge-bombing plot"],
    ["name"=>"Douglas L. Wright","sentenced_date"=>"2012-11-20","sentence_months"=>138,"charges"=>"18 USC 2332a use of weapons of mass destruction [2 counts]; 18 USC 844(h) use of explosive materials - 2012 Cleveland 5 May Day bridge-bombing plot"],

    // ---- TPP row 363 - Eric Oseland (RNC 8) ----
    ["name"=>"Eric Oseland","sentenced_date"=>"2010-10-19","sentence_months"=>3,"charges"=>"Minnesota state conspiracy charges - 2008 Republican National Convention Welcoming Committee organizing; terrorism enhancement dropped, all felonies dropped"],

    // ---- TPP row 110 - Kevin Olliff (Kevin Johnson) ----
    ["name"=>"Kevin Olliff","sentenced_date"=>"2014-07-08","sentence_months"=>36,"charges"=>"18 USC 43 force, violence, and threats involving animal enterprises (AETA) [2 counts] - 2013 Morris IL mink-farm release of approximately 2,000 mink. Sentence reflected 14-month credit for state-court time and $200,000 in joint restitution with Tyler Lang."],
    ["name"=>"Kevin Johnson","sentenced_date"=>"2014-07-08","sentence_months"=>36,"charges"=>"18 USC 43 AETA [2 counts] - 2013 Morris IL mink-farm release. 14-month state credit; $200,000 joint restitution with Tyler Lang."],

    // ---- TPP row 121 - Michael Markus / Rattler ----
    ["name"=>"Michael Markus","sentenced_date"=>"2018-09-20","sentence_months"=>36,"charges"=>"Federal civil disorder and use of fire to commit a federal felony - October 27, 2016 NoDAPL Standing Rock Backwater Bridge barricade fire"],
    ["name"=>"Michael Mateo Markus","sentenced_date"=>"2018-09-20","sentence_months"=>36,"charges"=>"18 USC 231(a)(3) civil disorder; 18 USC 844(h) use of fire to commit a federal felony - October 27, 2016 NoDAPL Standing Rock Backwater Bridge barricade fire"],
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
echo "TPP batch 4 backfill (covering missed entries from batches 2-4) complete."
