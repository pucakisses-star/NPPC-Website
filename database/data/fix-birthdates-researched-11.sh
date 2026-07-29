#!/usr/bin/env bash
#
# RESEARCHED BIRTHDATES, ROUND 11 -- eleventh chunk: 36 names
# researched, 6 records get a date. Every date was found STATED in a
# source; nothing is derived from a press "age N".
#
# THE LEDGER (slug -- date -- source):
#
#   edward-sanders       b. 1939-08-17
#       en.wikipedia.org/wiki/Ed_Sanders -- the poet, jailed for the
#       1961 Polaris submarine swim protest at New London.
#   william-durkin       b. 1949-07-26  d. 2021-04-18
#       North Shore funeral home obituary for William A. Durkin Jr.;
#       the Urban Milwaukee memorial confirms he was the Chicago 15
#       draft-file burner.
#   siobhan-browne       b. 1965-04-27
#       Broward Palm Beach New Times profile of the Florida IRA
#       gun-running case places her 34th birthday on April 27 amid
#       the spring 1999 purchases -- a stated birthday, 1965.
#   mark-warren-sands    b. 1951-06-20
#       Phoenix New Times profile published June 21, 2001: he
#       "turned 50 on June 20" -- a stated birthday.
#   caleb-a-brown        b. 2000-06-29
#       Wisconsin Circuit Court Access, Dane County 2024CF001019
#       (the UW encampment felony case). Press said "age 24"; the
#       court record shows that was off by one -- exactly why
#       age-derived years are banned here.
#   leroy-pinkett                       d. 1955-12-05
#       Justice for the 24th profile of the court-martialed 24th
#       Infantry private. His birth stays null: the profile and his
#       WWI draft card state CONFLICTING birth dates (Feb 23, 1894
#       vs June 1895), and conflicts are not resolved by guessing.
#
# EXCLUDED: a Suzanne Muscara date whose only source is a
# people-search data broker -- refused, consistent with every earlier
# round. A supporter-article birthday initially looking like Jonathan
# Darnel belongs to his co-defendant Will Goodman (and carries no
# year, so it is unusable anyway).
#
# RESEARCHED AND LEFT NULL (29 more names): Corey Smith, Darnel,
# Lili Marie Holland, Ryan Daniel Lewis, Jerry Gardner, Christianson,
# Eva Rose Holland, Geraghty, Klimek, Vassilatos, Zink,
# Picon-Rodriguez, Nolley-Hall, John Earl Johnson, Baldwin, Loren
# Reed, Patel, Czernik (tracking-site date refused), Gill, Solis
# Jordan, Wotulo, Newbins, Wyland, Arcila-Ramirez, Elizabeth Just,
# Troff, Musi, Tchibassa, McManigal.
#
# A record whose date is already set is left alone and reported --
# this script only fills blanks.
#
# Run from the repo root:
#   bash database/data/fix-birthdates-researched-11.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$rows = [
    ["slug" => "edward-sanders",    "birth" => [1939, 8, 17],  "death" => null,           "src" => "Wikipedia, Ed Sanders"],
    ["slug" => "william-durkin",    "birth" => [1949, 7, 26],  "death" => [2021, 4, 18],  "src" => "Obituary / Urban Milwaukee memorial"],
    ["slug" => "siobhan-browne",    "birth" => [1965, 4, 27],  "death" => null,           "src" => "New Times, Irish Sting profile (stated birthday)"],
    ["slug" => "mark-warren-sands", "birth" => [1951, 6, 20],  "death" => null,           "src" => "Phoenix New Times (stated birthday)"],
    ["slug" => "caleb-a-brown",     "birth" => [2000, 6, 29],  "death" => null,           "src" => "Wisconsin Circuit Court, Dane 2024CF001019"],
    ["slug" => "leroy-pinkett",     "birth" => null,           "death" => [1955, 12, 5],  "src" => "Justice for the 24th profile"],
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
