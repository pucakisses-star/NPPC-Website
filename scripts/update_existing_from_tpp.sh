#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/update_existing_from_tpp.sh
#
# For prisoners that already existed in the database when the
# Prosecution Project import ran (and were skipped with EXISTS:),
# this script *fills in* missing fields on their canonical case row
# from the editor-supplied TPP data. It NEVER overwrites a value
# that is already set on the case - only writes when the field is
# null or empty. Idempotent.
set -e

php artisan tinker --execute='
$rows = [
    ["name"=>"Rodney Adam Coronado","sentenced_date"=>"1993-07-15","sentence_months"=>57,"charges"=>"Federal arson, conspiracy, racketeering, extortion, and explosives charges - 1992 ALF firebombing of Michigan State University animal-research office, East Lansing"],
    ["name"=>"Peter Daniel Young","sentenced_date"=>"1998-09-16","sentence_months"=>24,"charges"=>"Federal interfering with interstate commerce [4 counts] and disrupting an animal enterprise [2 counts] - 1997 release of thousands of mink from three Wisconsin farms"],
    ["name"=>"Justin Clayton Samuel","sentenced_date"=>"1998-09-16","sentence_months"=>24,"charges"=>"Federal interfering with interstate commerce [4 counts] and disrupting an animal enterprise [2 counts] - 1997 release of thousands of mink from three Wisconsin farms"],
    ["name"=>"Jeffrey Luers","sentenced_date"=>"2000-06-16","sentence_months"=>266,"charges"=>"Oregon state arson in the first degree [3 counts]; unlawful manufacture of a destructive device [2 counts]; unlawful possession of a destructive device [2 counts]; attempted arson in the first degree [2 counts]; conspiracy to commit murder, treason, or a class A felony [2 counts] - 2000 Eugene Chevrolet dealership SUV firebombing"],
    ["name"=>"Craig \"Critter\" Marshall","sentenced_date"=>"2000-06-16","sentence_months"=>65,"charges"=>"Oregon state unlawful manufacture and possession of destructive devices, attempted arson in the first degree, criminal mischief, and arson in the first degree [3 counts] - 2000 Eugene Chevrolet dealership SUV firebombing"],
    ["name"=>"Tre Arrow","sentenced_date"=>"2007-08-13","sentence_months"=>78,"charges"=>"Federal obstruction, delay, and affecting commerce by extortion and violence - 2001 ELF arson of Ross Island Sand & Gravel cement trucks in Portland Oregon"],
    ["name"=>"William \"Avalon\" Rodgers","sentenced_date"=>null,"sentence_months"=>null,"charges"=>"Federal Operation Backfire arson of a government facility - died in custody (suicide) at Coconino County Jail, Flagstaff AZ on December 21, 2005 before trial"],
    ["name"=>"William Cottrell","sentenced_date"=>"2005-04-18","sentence_months"=>100,"charges"=>"Federal arson - August 2003 ELF-claimed firebombing of approximately 125 SUVs and one commercial building in the Los Angeles California area"],
    ["name"=>"Andrew Stepanian","sentenced_date"=>"2006-09-12","sentence_months"=>36,"charges"=>"Federal Animal Enterprise Protection Act conspiracy to disrupt an animal-testing enterprise - SHAC 7 Huntingdon Life Sciences campaign"],
    ["name"=>"Kevin Kjonaas","sentenced_date"=>"2006-09-12","sentence_months"=>72,"charges"=>"Federal Animal Enterprise Protection Act, conspiracy to engage in interstate stalking, interstate stalking [3 counts], and conspiracy to use telecommunications devices to harass - SHAC 7"],
    ["name"=>"Lauren Gazzola","sentenced_date"=>"2006-09-12","sentence_months"=>52,"charges"=>"Federal Animal Enterprise Protection Act, conspiracy to engage in interstate stalking, interstate stalking [3 counts], and conspiracy to use telecommunications devices to harass - SHAC 7"],
    ["name"=>"Jacob Conroy","sentenced_date"=>"2006-09-12","sentence_months"=>48,"charges"=>"Federal Animal Enterprise Protection Act, conspiracy to engage in interstate stalking, interstate stalking, and conspiracy to use telecommunications devices to harass - SHAC 7"],
    ["name"=>"Joshua Harper","sentenced_date"=>"2006-09-12","sentence_months"=>36,"charges"=>"Federal Animal Enterprise Protection Act and conspiracy to use telecommunications devices to harass - SHAC 7"],
    ["name"=>"Darius Fullmer","sentenced_date"=>"2006-09-12","sentence_months"=>12,"charges"=>"Federal Animal Enterprise Protection Act conspiracy - SHAC 7"],
    ["name"=>"Eric McDavid","sentenced_date"=>"2008-05-08","sentence_months"=>235,"charges"=>"18 USC 844(n) Conspiracy to damage and destroy property by explosive or fire - 2006 FBI-informant-driven plot to bomb a federal dam, fish hatchery, and cell phone towers"],
    ["name"=>"Zachary Jenson","sentenced_date"=>"2007-09-15","sentence_months"=>6,"charges"=>"18 USC 844(n) Conspiracy to damage and destroy property by explosive or fire - 2006 FBI-informant-driven plot"],
    ["name"=>"Lauren Weiner","sentenced_date"=>"2007-08-08","sentence_months"=>0,"charges"=>"18 USC 844(n) Conspiracy to damage and destroy property by explosive or fire - 2006 FBI-informant-driven plot (15 days served)"],
    ["name"=>"Darren Thurston","sentenced_date"=>"2007-05-22","sentence_months"=>37,"charges"=>"Federal conspiracy to commit arson and destruction of an energy facility, and arson of a government facility - Operation Backfire"],
    ["name"=>"Rebecca Rubin","sentenced_date"=>"2014-01-27","sentence_months"=>60,"charges"=>"Federal conspiracy to commit arson, arson [8 counts], arson of a government building, and use of a destructive device - Operation Backfire"],
    ["name"=>"Nathan Block","sentenced_date"=>"2007-05-25","sentence_months"=>92,"charges"=>"Federal conspiracy to commit arson, arson [47 counts], and attempted arson - Operation Backfire (with terrorism enhancement)"],
    ["name"=>"Joyanna Zacher","sentenced_date"=>"2007-05-25","sentence_months"=>92,"charges"=>"Federal conspiracy to commit arson, arson [35 counts], attempted arson, and use of a destructive device - Operation Backfire (with terrorism enhancement)"],
    ["name"=>"Chelsea Gerlach","sentenced_date"=>"2007-05-25","sentence_months"=>108,"charges"=>"Federal conspiracy to commit arson and destruction of energy facility, arson [15 counts], destruction of an energy facility, attempted arson, and arson [8 counts] - Operation Backfire"],
    ["name"=>"Kendall Tankersley","sentenced_date"=>"2007-05-25","sentence_months"=>46,"charges"=>"Federal conspiracy to commit arson and destruction of an energy facility, attempted arson, and arson - Operation Backfire"],
    ["name"=>"Daniel McGowan","sentenced_date"=>"2007-06-04","sentence_months"=>84,"charges"=>"Federal conspiracy to commit arson, arson, and use of a destructive device [14 counts] - Operation Backfire (with terrorism enhancement)"],
    ["name"=>"Stanislas Meyerhoff","sentenced_date"=>"2007-05-22","sentence_months"=>156,"charges"=>"Federal conspiracy to commit arson, arson [51 counts], destruction of an energy facility [2 counts], and use of a destructive device [2 counts] - Operation Backfire"],
    ["name"=>"Jonathan Paul","sentenced_date"=>"2007-06-05","sentence_months"=>51,"charges"=>"Federal conspiracy to commit arson and destruction of an energy facility, and arson - Operation Backfire"],
    ["name"=>"Kevin Tubbs","sentenced_date"=>"2007-05-24","sentence_months"=>151,"charges"=>"Federal conspiracy to commit arson, arson [40 counts], and attempted arson [2 counts] - Operation Backfire (with terrorism enhancement)"],
    ["name"=>"Jennifer Kolar","sentenced_date"=>"2008-04-04","sentence_months"=>60,"charges"=>"Federal conspiracy to commit arson, attempted arson, arson, and use of a destructive device - Operation Backfire (with terrorism enhancement)"],
    ["name"=>"Lacey Phillabaum","sentenced_date"=>"2008-04-04","sentence_months"=>36,"charges"=>"Federal conspiracy to commit arson, arson, and use of a destructive device - Operation Backfire"],
    ["name"=>"Grant Barnes","sentenced_date"=>"2007-07-23","sentence_months"=>144,"charges"=>"Colorado state use of explosives in the commission of a felony [6 counts], second-degree arson [2 counts], and possession of an explosive device [7 counts] - 2007 Cherry Creek Denver SUV firebombings"],
    ["name"=>"Marie Mason","sentenced_date"=>"2009-02-05","sentence_months"=>262,"charges"=>"18 USC 844(n) conspiracy to commit arson; 18 USC 844(f)(1) committing aggravated arson; 18 USC 844(i) arson - 1999 New Years Eve Michigan State University arson and 2000 Mesick Michigan logging-equipment arson"],
    ["name"=>"Frank Ambrose","sentenced_date"=>"2009-04-21","sentence_months"=>108,"charges"=>"18 USC 844(n) conspiracy to commit arson; 18 USC 844(i) arson - 1999 New Years Eve Michigan State University arson and 2000 Mesick logging-equipment arson"],
    ["name"=>"Tim DeChristopher","sentenced_date"=>"2011-07-26","sentence_months"=>21,"charges"=>"30 USC 195 violation of Federal Onshore Oil and Gas Leasing Act; 18 USC 1001 making a false statement - 2008 BLM auction protest action, Salt Lake City"],
    ["name"=>"Briana Waters","sentenced_date"=>"2012-06-22","sentence_months"=>48,"charges"=>"Federal arson [2 counts], conspiracy to commit arson, possession of an unregistered firearm [2 counts], and use of a destructive device [4 counts] - 2001 ELF arson of the University of Washington Center for Urban Horticulture"],
    ["name"=>"Joseph Dibee","sentenced_date"=>"2022-08-09","sentence_months"=>0,"charges"=>"Federal Operation Backfire conspiracy to commit arson and destruction of an energy facility, and arson - captured in Cuba May 2018, time-served release after pretrial detention"],
    ["name"=>"Walter Bond","sentenced_date"=>"2011-02-11","sentence_months"=>60,"charges"=>"18 USC 844(i) use of fire to damage and destroy property in interstate commerce; 18 USC 43 use of force, violence, and threats involving an animal enterprise (AETA) - April 2010 Sheepskin Factory arson, Glendale Colorado"],
    ["name"=>"Douglas Joshua Ellerman","sentenced_date"=>"1999-09-02","sentence_months"=>84,"charges"=>"Federal explosives charges - 1997 pipe-bombing of the Fur Breeders Agricultural Cooperative offices, Sandy Utah"],
    ["name"=>"Suzanne Savoie","sentenced_date"=>"2007-05-23","sentence_months"=>51,"charges"=>"Federal conspiracy to commit arson, arson [13 counts], attempted arson, and use of a destructive device - Operation Backfire (with terrorism enhancement)"],
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
echo "Existing-prisoner update from TPP data complete."
