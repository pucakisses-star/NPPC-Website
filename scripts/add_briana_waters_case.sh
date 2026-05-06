#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_briana_waters_case.sh
#
# Adds a case row to Briana Waterss existing prisoner record. The
# round6 prisoner:add command was a no-op for her because she
# already existed in the database from the Airtable import, so
# the case data attached to that command never landed in DB.
#
# Case facts (from round6 add command, repeated here):
#   - Charges: federal arson - May 21, 2001 ELF action at the
#     University of Washington Center for Urban Horticulture
#   - Arrest:  2006-03-15
#   - Began incarceration: 2008-03-06 (sentencing)
#   - First conviction: March 2008 federal jury verdict, 6 yrs
#   - Conviction reversed on appeal in 2009 due to suppressed
#     evidence about a government witness
#   - 2012 guilty plea on retrial; resentenced
#   - Released: 2014-03-31
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Briana Waters")->first();
if (!$p) {
    echo "ERROR: Briana Waters not found\n";
    exit(1);
}

if ($p->cases()->exists()) {
    echo "Briana Waters already has cases (count={$p->cases()->count()}); skipping.\n";
    exit(0);
}

$inst = App\Models\Institution::firstOrCreate(
    ["name" => "Federal Bureau of Prisons (location varied)"]
);

App\Models\PrisonerCase::create([
    "prisoner_id"        => $p->id,
    "institution_id"     => $inst->id,
    "charges"            => "Federal arson - charged with serving as a lookout during the May 21, 2001 Earth Liberation Front arson at the University of Washington Center for Urban Horticulture in Seattle, the largest single ELF action in the United States. She was the only Operation Backfire defendant to take her case to trial.",
    "arrest_date"        => "2006-03-15",
    "incarceration_date" => "2008-03-06",
    "sentenced_date"     => "2008-06-19",
    "release_date"       => "2014-03-31",
    "sentence"           => "6 years federal prison (2008 conviction). Conviction reversed on appeal in 2009 because the government had suppressed evidence about a witness. Retried; pleaded guilty in 2012 to a reduced count and was resentenced; released 2014.",
    "convicted"          => "Yes - federal jury verdict 2008 (reversed on appeal 2009); 2012 guilty plea on retrial",
]);

echo "Inserted Briana Waters case (2008-2014, federal arson, ELF UWCUH).\n";
'

echo
echo "Briana Waters case add complete."
