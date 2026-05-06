#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/fix_branden_wolfe_case.sh
#
# Branden Wolfes case row was missing arrest / incarceration dates
# and a full sentence narrative; only the audit-set release date
# (2024-10-15) was attached. This script finds his existing case
# row (or creates one) and fills in the missing fields:
#
#   - Action: May 28, 2020 - among the crowd at the Minneapolis
#     Police 3rd Precinct three days after George Floyds murder;
#     pushed a barrel into a fire already burning at the precinct
#     entrance to accelerate it. Also took body armor, weapons,
#     and ammunition belonging to MPD.
#   - Arrest: June 3, 2020 (St. Paul, Minnesota).
#   - Plea: federal aiding and abetting arson, April 2021.
#   - Sentenced: May 4, 2021 to 41 months federal prison + 2 years
#     supervised release + $12M restitution.
#   - Released: October 15, 2024 from federal custody (per BOP).
#   - BOP register number: 22425-041.
#
# Source: ATF press release, May 4, 2021; CNN; Star Tribune;
# Bureau of Prisons inmate locator.
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Branden Wolfe")->first();
if (!$p) {
    echo "ERROR: Branden Wolfe not found\n";
    exit(1);
}
echo "Found prisoner: {$p->name} (id={$p->id})\n";

if (!$p->inmate_number) { $p->inmate_number = "22425-041"; }
if ($p->in_custody)     { $p->in_custody    = false; }
if (!$p->released)      { $p->released      = true; }
$p->save();

$inst = App\Models\Institution::firstOrCreate(
    ["name" => "Federal Bureau of Prisons (location varied)"]
);

$case = $p->cases()->orderBy("created_at")->first();
if (!$case) {
    $case = new App\Models\PrisonerCase(["prisoner_id" => $p->id]);
    echo "No existing case; creating one.\n";
}

$case->institution_id     = $inst->id;
$case->charges            = "Federal aiding and abetting arson - May 28, 2020 burning of the Minneapolis Police Department Third Precinct three days after the police killing of George Floyd. Federal prosecutors alleged Wolfe pushed a barrel into a fire that had already been set at the precinct entrance to accelerate it; he also took body armor, weapons, and ammunition belonging to MPD from the scene.";
$case->arrest_date        = "2020-06-03";
$case->incarceration_date = "2021-05-04";
$case->sentenced_date     = "2021-05-04";
$case->release_date       = "2024-10-15";
$case->sentence           = "41 months federal prison + 2 years supervised release + approximately $12 million in restitution. Sentenced May 4, 2021 by U.S. District Court for the District of Minnesota; released October 15, 2024. BOP register number 22425-041.";
$case->convicted          = "Yes - guilty plea, April 2021, U.S. District Court for the District of Minnesota";
$case->save();

echo "Updated case id={$case->id}: arrest=2020-06-03 incarc=2021-05-04 release=2024-10-15 imprisoned_for_days={$case->imprisoned_for_days}\n";
'

echo
echo "Branden Wolfe case fix complete."
