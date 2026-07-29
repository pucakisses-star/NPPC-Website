#!/usr/bin/env bash
#
# RESEARCHED BIRTHDATES, ROUND 12 -- twelfth chunk: 36 names researched,
# 4 records get a date. Every date was found STATED in a source; nothing
# is derived from a press "age N".
#
# THE LEDGER (slug -- date -- source):
#
#   ted-goertzel         b. 1942-11-20
#       Library of Congress name authority n79118966 for Goertzel, Ted
#       George, recording "data sheet (b. 11-20-42)" -- i.e. supplied by
#       him. Agrees with an Antioch B.A. in 1964 and with being 18 at
#       the April 1961 action. NOTE: the infobox of his Wikipedia
#       article says 1944, but the body of that same article, its
#       category and Wikidata all say 1942, so that is a typo in one
#       field rather than a competing source.
#   donald-mark-johnson  b. 1990-12-12
#       Pennsylvania UJS docket CP-02-CR-0005738-2024 (Allegheny
#       County) states Date Of Birth 12/12/1990 -- and it is HIS case:
#       the June 2, 2024 offence date, the University of Pittsburgh
#       police as arresting agency and the negotiated plea with two
#       years probation all match the record.
#   jacob-david-bardwell-sherman b. 1982-01      (month precision)
#       Willamette Week retrospective on his Earth Liberation Front
#       case: "born in January 1982". Same Eagle Creek / Ross Island
#       arsons as the record.
#   bob-graf             b. 1943                 (year precision)
#       His own biographical site, Keeping the Faith: Bob Graf, states
#       "Born in 1943" and confirms the Milwaukee 14 action, the
#       Jesuits and Casa Maria.
#
# EXCLUDED, AND WHY IT MATTERS. A Wisconsin court record gives a
# month-precision DOB (04-1973) for the only Aaron Ellringer in that
# state{39}s courts, resident of Eau Claire, whose age lines up with the
# indicted "Aaron Ellringer of Eau Claire". But it is a traffic case,
# not his federal case -- the link is name plus city plus age, exactly
# the basis on which the Ronald DeRisi obituary was refused in round 8.
# One standard or none: it stays out. If it should go in, it is a
# one-line addition to the rows below.
#
# Also refused: a William James Viehl birth month that appears only on
# a hostile tracking site, and Colinford Mattis birth years (1987 vs
# 1988) that appear only on auto-generated bio/people-search pages and
# look derived from reported ages.
#
# RESEARCHED AND LEFT NULL (32 names): Jenkins, Boulter, Sweeney,
# Durfee, Viehl, Rickey Johnson, Hobgood, Younes Arboleda (press ages
# themselves conflict), Navarrete Beltran, Miller-Castillo, Linda
# Rosenstock, McGovern, Gautschy, Bruce Thompson, Banol Ramos, Ed
# Lazar, Bishop, Bartels, Peace, Orlando Javier Ramirez, Jeffrey,
# Haniffa bin Osman, Schnell, Comiskey, Mattis, Cesario, Smulian,
# Ellringer, Yerganian, Walton, McIntyre, Woods.
#
# A record whose date is already set is left alone and reported --
# this script only fills blanks.
#
# Run from the repo root:
#   bash database/data/fix-birthdates-researched-12.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$rows = [
    ["slug" => "ted-goertzel",                 "birth" => [1942, 11, 20], "death" => null, "src" => "Library of Congress name authority n79118966"],
    ["slug" => "donald-mark-johnson",          "birth" => [1990, 12, 12], "death" => null, "src" => "PA UJS docket CP-02-CR-0005738-2024"],
    ["slug" => "jacob-david-bardwell-sherman", "birth" => [1982, 1],      "death" => null, "src" => "Willamette Week retrospective"],
    ["slug" => "bob-graf",                     "birth" => [1943],         "death" => null, "src" => "Keeping the Faith: Bob Graf"],
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
