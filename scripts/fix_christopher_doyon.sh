#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/fix_christopher_doyon.sh
#
# Christopher Doyon (Commander X) was an Anonymous-affiliated hacker held
# in federal pretrial detention in the Northern District of California
# from June 11, 2021 (date of his Mexico-to-US deportation) until June
# 28, 2022, when Judge Beth Labson Freeman sentenced him to 1 year of
# probation - which ran concurrent with his ~1 year of time served, so
# he walked out the same day.
#
# The existing case row has institution = "Guantanamo Bay Detention Camp"
# with state "Cuba" and the literal Filament/admin form template default
# mailing address ("Detainee name JTF-GTMO SJA PSC 210 ISN (detainee
# number here) FPO AA 34010"). That is unfilled placeholder data, not
# actual case data - Doyon was never anywhere near Guantanamo.
#
# Source: USA v. Doyon, 5:11-cr-00683 (N.D. Cal.); Wikipedia: Christopher
# Doyon; CourtListener docket entries for case 5:11-cr-00683.
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "like", "%Doyon%")->where(function ($q){
    $q->where("name", "like", "%Christopher%")->orWhere("aka", "like", "%Commander X%");
})->first();
if (!$p) {
    echo "ERROR: Christopher Doyon not found\n";
    exit(1);
}
echo "Found prisoner: {$p->name} (id={$p->id})\n";

// Replace the Guantanamo placeholder with the actual federal-pretrial facility
$inst = App\Models\Institution::firstOrCreate(
    ["name" => "Federal pretrial detention (Northern District of California)"],
    ["city" => "San Jose", "state" => "California"]
);

$cases = $p->cases()->get();
foreach ($cases as $case) {
    $case->institution_id = $inst->id;
    // Improve the charges / sentence text while were here
    $case->charges = "Federal computer fraud and conspiracy - distributed denial-of-service takedown of the Santa Cruz County, California website for approximately 30 minutes in December 2010, in protest of the citys ordinance criminalizing sleeping on public land. Indicted in N.D. Cal. (5:11-cr-00683); fled to Canada Feb 2012, granted asylum, lived in exile until apprehended in Mexico and deported to the U.S. on June 11, 2021.";
    $case->sentence = "Federal pretrial detention from June 11, 2021 through June 28, 2022 (~1 year 17 days). On June 28, 2022, Judge Beth Labson Freeman sentenced him to 1 year of probation on a superseding misdemeanor information (prosecution had recommended 15 years federal prison); the probation ran concurrent with his time served and he was released the same day.";
    $case->convicted = "Yes - guilty plea, May 2022, U.S. District Court for the Northern District of California (5:11-cr-00683)";
    $case->save();
    echo "Updated case id={$case->id}: institution_id={$case->institution_id}, imprisoned_for_days={$case->imprisoned_for_days}\n";
}
'

echo
echo "Christopher Doyon fix complete."
