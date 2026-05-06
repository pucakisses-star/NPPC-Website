#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/fix_ruchell_magee.sh
#
# Fixes Ruchell "Cinque" Magee's case row:
#   - Existing case has institution "Charlestown State Prison" (Massachusetts),
#     a Boston prison that was closed in 1955 — clearly an Airtable-import
#     data error since Magee was a California state prisoner for 60 years
#   - Existing case has no incarceration_date set, so the booted hook leaves
#     imprisoned_for_days null and the page shows no Time Imprisoned counter
#
# Sources:
#   - Magee was arrested on March 23, 1963 in Los Angeles after a fight over
#     a woman and a $10 bag of marijuana; convicted of kidnapping and robbery,
#     sentenced to life
#   - Held continuously in California state custody for 60 years (San Quentin,
#     Folsom, Soledad, Mule Creek, CMF Vacaville at end)
#   - Was the only survivor of the August 7, 1970 Marin County Courthouse
#     takeover led by Jonathan Jackson; wounded
#   - Released July 28, 2023 from CMF Vacaville under California's 2023
#     compassionate-release law
#   - Died October 17, 2023, 81 days after release
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "like", "Ruchell%Magee%")->first();
if (!$p) {
    echo "ERROR: Ruchell Magee not found\n";
    exit(1);
}
echo "Found prisoner: {$p->name} (id={$p->id})\n";

// Fix the institution to the actual CA facility where he was released
$inst = App\Models\Institution::firstOrCreate(
    ["name" => "California Medical Facility, Vacaville"],
    ["city" => "Vacaville", "state" => "California"]
);

// Locate his existing case row
$case = $p->cases()->orderBy("created_at")->first();
if (!$case) {
    echo "No case row exists; creating one.\n";
    $case = new App\Models\PrisonerCase(["prisoner_id" => $p->id]);
}

$case->institution_id     = $inst->id;
$case->charges            = "California state aggravated kidnapping and robbery, 1963 Los Angeles arrest after a fight in a nightclub with Ben Brown over a woman and a \$10 bag of marijuana; conviction obtained over Magees protests of police misconduct (LAPD beat him so severely he was hospitalized for three to five days). Conviction reversed on appeal in 1964; reinstated in 1965 after a retrial in which the judge had Magee bound and gagged in front of the jury. Additional 1971-73 California state kidnapping conviction arising from the August 7, 1970 Marin County Courthouse takeover led by Jonathan Jackson, in which Magee — a court witness that day — was the only survivor.";
$case->arrest_date        = "1963-09-01";
$case->incarceration_date = "1963-09-01";
$case->release_date       = "2023-07-28";
$case->sentence           = "Indeterminate 7 years to life; held continuously 60 years (San Quentin, Folsom, Soledad, Mule Creek, CMF Vacaville); released under California 2023 compassionate-release law. (Exact 1963 arrest day is approximate — secondary sources put it ~6 months after his early-1963 arrival in LA from Louisiana parole; the 217 Cal.App.2d 443 appellate opinion would state the verbatim offense date but is not web-accessible.)";
$case->convicted          = "Yes - California state aggravated kidnapping and robbery, 1965 Superior Court of Los Angeles County retrial; subsequent 1973 conviction on Marin Courthouse charges";
$case->save();

echo "Case updated; imprisoned_for_days now = {$case->imprisoned_for_days} (~21,879 = ~59 years 10 months)\n";

// Also make sure the death date is set
if (!$p->death_date) {
    $p->death_date = "2023-10-17";
    $p->saveQuietly();
    echo "Set death_date to 2023-10-17.\n";
}
'

echo
echo "Ruchell Magee fix complete."
