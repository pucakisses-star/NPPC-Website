#!/usr/bin/env bash
#
# RESEARCHED BIRTHDATES, ROUND 2 -- second chunk of the individual
# research. Twenty-four names researched; four dates found STATED in a
# source. Nothing is derived from an "age N" mention.
#
# THE LEDGER (slug -- date -- source):
#
#   benjamin-sasway      b. 1960-12-09
#       U.S. Solicitor General brief in Sasway v. United States
#       (justice.gov/d9/osg/briefs/1984/01/01/sg840131.txt):
#       "Petitioner, a college student born on December 9, 1960" --
#       the same draft-registration case as his record.
#   jack-gaveel          b. 1889                       (year precision)
#       kenyonzimmer.com/red-scare-deportees/galeotti-to-gazeyog/
#       states "Born: 1889, Amsterdam, Netherlands".
#   sam-povff            b. 1878                       (year precision)
#       kenyonzimmer.com/red-scare-deportees/povff-to-prokopovich/
#       states "Born: 1878, Poltava, Russia".
#   william-wright-jr    d. 1990                       (year precision)
#       Wikipedia, Wilmington Ten: "William Wright died in 1990".
#       His birth date stays null -- only an age at sentencing exists.
#
# RESEARCHED AND LEFT NULL (sources state only ages, or nothing): the
# Akron July 2022 arrestees (Alrawajfeh, Eaton, Bolinger, Clark,
# Culver, Brown, Martin, Ali), Chase Grijalva, Kyle Wagner, Aline
# Espinosa-Villegas, Ivan Ferguson, Margaret E. Cornell, Thomas
# Jurgens, Jacob Kenison, Juan Galloza-Acevedo, Jerry Texiero, Spencer
# Anderson, Ronald Bridgeforth, John Mazurek. Two data-broker dates
# were rejected because the only case linkage ran through a hostile
# tracking site, which this project does not use.
#
# A record whose date is already set is left alone and reported --
# this script only fills blanks.
#
# Run from the repo root:
#   bash database/data/fix-birthdates-researched-2.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$rows = [
    ["slug" => "benjamin-sasway",   "birth" => [1960, 12, 9], "death" => null,   "src" => "US Solicitor General brief, Sasway v. United States"],
    ["slug" => "jack-gaveel",       "birth" => [1889],        "death" => null,   "src" => "Kenyon Zimmer, Red Scare Deportees"],
    ["slug" => "sam-povff",         "birth" => [1878],        "death" => null,   "src" => "Kenyon Zimmer, Red Scare Deportees"],
    ["slug" => "william-wright-jr", "birth" => null,          "death" => [1990], "src" => "Wikipedia, Wilmington Ten"],
];

$set = 0;
$kept = 0;
$missing = 0;

foreach ($rows as $row) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->first();
    if (! $p) {
        echo "  NOT FOUND: {$row["slug"]}\n";
        $missing++;
        continue;
    }

    $dirty = false;

    if ($row["birth"]) {
        if ($p->birthdate) {
            echo "  {$row["slug"]}: birthdate already set ({$p->formatPartialDate("birthdate")}) — left alone\n";
            $kept++;
        } else {
            $b = $row["birth"];
            $p->setPartialDate("birthdate", $b[0], $b[1] ?? null, $b[2] ?? null);
            $dirty = true;
        }
    }

    if ($row["death"]) {
        if ($p->death_date) {
            echo "  {$row["slug"]}: death date already set ({$p->formatPartialDate("death_date")}) — left alone\n";
        } else {
            $d = $row["death"];
            $p->setPartialDate("death_date", $d[0], $d[1] ?? null, $d[2] ?? null);
            $dirty = true;
        }
    }

    if ($dirty) {
        $p->save();
        $set++;
        echo "  {$row["slug"]}:"
            .($p->birthdate ? " birth {$p->formatPartialDate("birthdate")}" : "")
            .($p->death_date ? " death {$p->formatPartialDate("death_date")}" : "")
            ." [{$row["src"]}]\n";
    }
}

echo "\nRecords updated: {$set}\n";
echo "Left alone (date already present): {$kept}\n";
echo "Slugs not found: {$missing}\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
