#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_ronald_reed_case.sh
#
# Adds the missing case row to Ronald Reed (Black liberation
# organizer, Minneapolis-St. Paul) for his 2005 cold-case prosecution
# and 2006 conviction for the May 22, 1970 sniper killing of St. Paul
# Police Officer James Sackett. Reed and co-defendant Larry Clark
# were charged 35 years after the killing; conviction rested heavily
# on testimony of incarcerated witnesses who received sentence
# considerations. Reed has been held in Minnesota DOC custody
# (Stillwater) since his May 4, 2005 arrest.
#
# Source: State v. Reed, 737 N.W.2d 572 (Minn. 2007); Minnesota DOC
# offender locator.
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Ronald Reed")->first();
if (!$p) {
    echo "ERROR: Ronald Reed not found\n";
    exit(1);
}

if ($p->cases()->exists()) {
    echo "Ronald Reed already has cases (count={$p->cases()->count()}); skipping.\n";
    exit(0);
}

$inst = App\Models\Institution::firstOrCreate(
    ["name" => "Minnesota Correctional Facility - Stillwater"],
    ["city" => "Bayport", "state" => "Minnesota"]
);

App\Models\PrisonerCase::create([
    "prisoner_id"        => $p->id,
    "institution_id"     => $inst->id,
    "charges"            => "First-degree murder and conspiracy to commit first-degree murder of St. Paul Police Officer James Sackett, killed by a sniper shot while responding to a fabricated emergency call at 859 Hague Avenue in St. Paul on May 22, 1970. Reed and Larry Clark were charged in May 2005 in a cold-case prosecution following Reeds decades of civil-rights and Black liberation organizing in Minneapolis-St. Paul. The conviction relied largely on testimony of incarcerated witnesses who received sentence considerations.",
    "arrest_date"        => "2005-05-04",
    "incarceration_date" => "2005-05-04",
    "sentenced_date"     => "2006-10-09",
    "convicted"          => "Yes - Minnesota state jury verdict, Ramsey County District Court, July 18, 2006",
    "sentence"           => "Life in Minnesota state prison with possibility of parole after 30 years (per 1970 statute in effect at time of offense); sentenced October 9, 2006. Conviction affirmed: State v. Reed, 737 N.W.2d 572 (Minn. 2007).",
]);

echo "Inserted Ronald Reed case (2005 arrest / 2006 Sackett-killing conviction, life sentence).\n";
'

echo
echo "Ronald Reed case add complete."
