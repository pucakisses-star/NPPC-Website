#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/fix_cleveland_4_dates.sh
#
# All four Cleveland 4 defendants (Douglas Wright, Brandon Baxter,
# Connor Stevens, Joshua "Skelly" Stafford) were arrested on April 30,
# 2012 and held in federal pretrial detention without bail from that
# date through their sentencings - none was ever released on bond
# pre-trial. Per the federal docket (United States v. Wright,
# 1:12-cr-00238 N.D. Ohio), at the May 7, 2012 arraignment all
# defendants "agreed to being held in custody," and no defendant
# subsequently filed a bond motion that was granted.
#
# Earlier rounds of this database used sentencing-date as
# incarceration_date for these four, which under-reports their actual
# time imprisoned by ~6.7 months (April 30, 2012 to November 20, 2012
# pretrial period). Reset incarceration_date to the arrest date
# (2012-04-30) so the page-level Time Imprisoned counter is accurate.
#
# Source: Cleveland 4 federal docket entries via CourtListener for
# 1:12-cr-00238.
set -e

php artisan tinker --execute='
$names = [
    "Douglas Wright"   => "2012-04-30",
    "Brandon Baxter"   => "2012-04-30",
    "Connor Stevens"   => "2012-04-30",
    "Joshua Stafford"  => "2012-04-30",
];

$fixed = 0;
foreach ($names as $name => $newDate) {
    $p = App\Models\Prisoner::where("name", $name)->first();
    if (!$p) {
        echo "skip: prisoner {$name} not found\n";
        continue;
    }
    foreach ($p->cases as $case) {
        // Only touch the Cleveland 4 federal case row
        if (! str_contains((string) $case->charges, "Cuyahoga Valley")) {
            continue;
        }
        $oldDate = $case->incarceration_date?->toDateString() ?? "(null)";
        $case->incarceration_date = $newDate;
        $case->save();
        $fixed++;
        echo "fixed: {$name} (case id={$case->id}) incarceration_date {$oldDate} -> {$newDate}; imprisoned_for_days now = {$case->imprisoned_for_days}\n";
    }
}
echo "\nDone. {$fixed} case rows updated.\n";
'

echo
echo "Cleveland 4 incarceration-date fix complete."
