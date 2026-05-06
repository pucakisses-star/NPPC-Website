#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_joejoe_bowen_case.sh
#
# Adds the missing case row to Joe-Joe Bowen (Joseph Bowen),
# Pennsylvania prison-abolitionist political prisoner held in long-
# term solitary confinement. Bowen was originally convicted in 1971
# for the August 29, 1970 killing of Philadelphia Park Police Officer
# Frank Von Colln at a Fairmount Park guard booth. While imprisoned
# at Holmesburg Prison in Philadelphia on May 31, 1973, Bowen and
# Frederick "Smokey" Burton killed warden Patrick Curran and deputy
# warden Robert Fromhold during a Muslim-prisoner organizing dispute,
# adding two consecutive life sentences. In October 1981 Bowen
# attempted a hostage-taking escape from Graterford State Prison.
# He has been held continuously in Pennsylvania DOC custody since
# 1970, much of it in solitary confinement, and has been a leader of
# multiple hunger strikes against Pennsylvania DOC conditions.
#
# Source: Commonwealth v. Bowen, 461 Pa. 451 (1975); Commonwealth v.
# Bowen, 532 Pa. 569 (1992); Pennsylvania DOC inmate locator.
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Joe-Joe Bowen (Joseph Bowen)")
    ->orWhere("name", "like", "%Joe-Joe Bowen%")
    ->orWhere("name", "like", "%Joseph Bowen%")
    ->first();

if (!$p) {
    echo "ERROR: Joe-Joe Bowen / Joseph Bowen not found\n";
    exit(1);
}

if ($p->cases()->exists()) {
    echo "Bowen already has cases (count={$p->cases()->count()}); skipping.\n";
    exit(0);
}

$inst = App\Models\Institution::firstOrCreate(
    ["name" => "SCI Huntingdon"],
    ["city" => "Huntingdon", "state" => "Pennsylvania"]
);

App\Models\PrisonerCase::create([
    "prisoner_id"        => $p->id,
    "institution_id"     => $inst->id,
    "charges"            => "Pennsylvania first-degree murder of Philadelphia Park Police Officer Frank Von Colln, killed at a Fairmount Park guard booth on August 29, 1970 (original conviction, 1971). Pennsylvania first-degree murder of Holmesburg Prison warden Patrick Curran and deputy warden Robert Fromhold, killed inside the prison on May 31, 1973 in retaliation for warden interference with Muslim-prisoner organizing (second conviction, 1975). Additional charges arising from the October 1981 hostage-taking escape attempt at Graterford State Prison. Bowen has spent most of his confinement in long-term solitary and has led multiple hunger strikes against Pennsylvania DOC conditions.",
    "arrest_date"        => "1970-08-29",
    "incarceration_date" => "1970-08-29",
    "convicted"          => "Yes - multiple Pennsylvania state jury verdicts: 1971 (Von Colln); 1975 (Curran/Fromhold, affirmed Commonwealth v. Bowen, 461 Pa. 451 (1975)); 1982 (Graterford escape attempt).",
    "sentence"           => "Life without parole (Von Colln) plus two consecutive life sentences (Curran/Fromhold) plus additional time for the 1981 escape attempt. Held continuously in Pennsylvania DOC custody since August 1970; transferred among Holmesburg, Graterford, SCI Pittsburgh, SCI Greene, and SCI Huntingdon, predominantly in solitary confinement.",
]);

echo "Inserted Joe-Joe Bowen case (1970-present, life sentences, PA DOC).\n";
'

echo
echo "Joe-Joe Bowen case add complete."
