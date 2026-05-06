#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/fix_jacob_conroy_case.sh
#
# Fills in Jacob "Jake" Conroys case row. The earlier add-script
# backfill skipped him because he has an existing case row from
# the Airtable import with all dates and charges set to null, so
# the whereDoesntHave("cases") filter excluded him.
#
# Per Animal Liberation Frontline (Nov 2009 first-post-prison
# statement) and contemporaneous press: Conroy was the SHAC USA
# coordinator and webmaster, indicted May 26 2004 in the first
# federal Animal Enterprise Protection Act prosecution. The SHAC 7
# were convicted by a federal jury in Trenton in March 2006.
# Conroy was sentenced September 12 2006 to 48 months federal
# prison and was released from FCI Terminal Island on November 6
# 2009 (early to a Bay Area halfway house).
#
# Sources:
# - https://animalliberationfrontline.com/first-post-prison-statement-from-shac-7-prisoner-jacob-conroy/
# - https://en.wikipedia.org/wiki/Jake_Conroy
# - https://www.justice.gov/archive/usao/nj/Press/files/pdffiles/Older/shac0912rel.pdf
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Jacob Conroy")
    ->orWhere("name", "Jake Conroy")
    ->orWhere("name", "like", "%Conroy%")
    ->first();
if (!$p) {
    echo "ERROR: Jacob Conroy not found\n";
    exit(1);
}
echo "Found prisoner: {$p->name} (id={$p->id})\n";

$inst = App\Models\Institution::firstOrCreate(
    ["name" => "Federal Correctional Institution, Terminal Island"],
    ["city" => "San Pedro", "state" => "California"]
);

$case = $p->cases()->orderBy("created_at")->first();
if (!$case) {
    $case = new App\Models\PrisonerCase(["prisoner_id" => $p->id]);
    echo "No existing case; creating one.\n";
}

$case->institution_id     = $inst->id;
$case->charges            = "Federal Animal Enterprise Protection Act and interstate stalking - first federal prosecution under the Animal Enterprise Protection Act / Animal Enterprise Terrorism Act. Conroy served as the coordinator and webmaster of Stop Huntingdon Animal Cruelty (SHAC) USA, prosecuted for using the SHAC website and protests to incite stalking and harassment of personnel at Huntingdon Life Sciences and its U.S. clients.";
$case->arrest_date        = "2004-05-26";
$case->incarceration_date = "2006-09-12";
$case->sentenced_date     = "2006-09-12";
$case->release_date       = "2009-11-06";
$case->sentence           = "48 months federal prison; served Sept 12 2006 - Nov 6 2009 at FCI Terminal Island in San Pedro, California, then transferred to a Bay Area halfway house for the remainder.";
$case->convicted          = "Yes - federal jury verdict, U.S. District Court for the District of New Jersey, March 2006";
$case->save();

$dirty = false;
if ($p->in_custody) { $p->in_custody = false; $dirty = true; }
if (!$p->released)  { $p->released   = true;  $dirty = true; }
if ($dirty) { $p->save(); }

echo "Updated case id={$case->id}: arrest=2004-05-26 incarc=2006-09-12 release=2009-11-06 imprisoned_for_days={$case->imprisoned_for_days}\n";
'

echo
echo "Jacob Conroy case fix complete."
