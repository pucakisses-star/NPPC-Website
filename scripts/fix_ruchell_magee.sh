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
$case->charges            = "California Penal Code Section 209 (kidnapping for the purpose of robbery — life sentence under pre-1972 California law) and Section 211 (first-degree robbery), as a second felony offender. 1963 Los Angeles arrest after a nightclub dispute with Ben Brown over a woman and a \$10 bag of marijuana; LAPD beat Magee so severely he was hospitalized 3-5 days. First conviction was reversed by the California Court of Appeal, Second District, in an unpublished opinion (People v. Magee, Crim. No. 9376, December 18, 1964) on the ground that the joinder of a separate, unrelated robbery count against his co-defendant had unduly prejudiced Magee. On remand he was retried alone and re-convicted; sentence was pronounced on September 2, 1965. Magee filed a notice of appeal but moved to dismiss it on December 10, 1965; the California Court of Appeal dismissed the appeal on December 17, 1965. Subsequent California state kidnapping conviction arising from the August 7, 1970 Marin County Courthouse takeover led by Jonathan Jackson — in which Magee was the only survivor — was tried separately in San Francisco Superior Court (People v. Ruchell Magee, Sup. Ct. No. 83668) with sentence in 1973.";
$case->arrest_date        = "1963-03-23";
$case->incarceration_date = "1963-03-23";
$case->sentenced_date     = "1965-09-02";
$case->release_date       = "2023-07-28";
$case->sentence           = "Indeterminate 7 years to life on the Section 209 kidnapping count; held continuously 60 years 4 months 5 days (San Quentin, Folsom, Soledad, Mule Creek, CMF Vacaville); released under California 2023 compassionate-release law. Sentence date Sept 2, 1965 confirmed via Magee v. Nelson, 455 F.2d 275 (9th Cir. 1972) - Caselaw Access Project.";
$case->convicted          = "Yes - 1965 Superior Court of Los Angeles County retrial conviction (Sept 2, 1965 sentence) under Cal. Penal Code Sections 209 and 211; subsequent 1973 conviction on Marin Courthouse charges";
$case->save();

echo "Case updated; imprisoned_for_days now = {$case->imprisoned_for_days} (~22,041 = ~60 years 4 months)\n";

// Also make sure the death date is set
if (!$p->death_date) {
    $p->death_date = "2023-10-17";
    $p->saveQuietly();
    echo "Set death_date to 2023-10-17.\n";
}
'

echo
echo "Ruchell Magee fix complete."
