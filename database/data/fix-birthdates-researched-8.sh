#!/usr/bin/env bash
#
# RESEARCHED BIRTHDATES, ROUND 8 -- eighth chunk: 36 names researched,
# 8 records get a date. Every date was found STATED in a source;
# nothing is derived from a press "age N".
#
# THE LEDGER (slug -- date -- source):
#
#   eugene-keyes         b. 1941-10-24  d. 2025-05-24
#       en.wikipedia.org/wiki/Gene_Keyes -- the CNVA Polaris Action
#       activist jailed for boarding a submarine at New London, 1961.
#   cortez-aaron-rice    b. 1989-09-21
#       Wisconsin Circuit Court Access, Waukesha Co. 2021CF001868
#       (his fugitive arrest on the Hennepin County warrant).
#   christopher-lay      b. 1985-01-21
#       Oklahoma DOC inmate record (DOC 515483, via mirror; the
#       official lookup is captcha-gated); consistent with the OCCA
#       opinion calling him 19 on 2004-05-24.
#   wil-casey-floyd      b. 1984-12-21
#       Wisconsin Circuit Court Access record for his distinctive
#       name; Wisconsin residence and ages match the Seattle May Day
#       case exactly.
#   chioke-auden-fugate  b. 1999-05-29
#       Asheville PD release DOB as carried by Queen City News/WNCT
#       coverage of the July 4, 2022 Vance Monument arrests.
#   jayma-abdoo          b. 1951        d. 2006        (year precision)
#       Swarthmore Peace Collection finding aid for her papers:
#       "Jayma Abdoo, 1951-2006, b. Philadelphia"; the PBS POV
#       Camden 28 film update confirms the death year.
#   william-mackie       b. 1908                       (year precision)
#       Justice Douglas dissent in Niukkanen v. McAlexander, 362 U.S.
#       390 (1960): "Petitioner was born in Finland in 1908" --
#       Mackie is William Niukkanen.
#   sarah-tosi                          d. 2006-04-15
#       Wellesley Magazine alumnae memorials, class of 1973 --
#       matching her record. Birth date stays null.
#
# EXCLUDED ON IDENTITY OR CONFLICT GROUNDS:
#   - Ronald DeRisi: a funeral-home obituary fits his reported ages
#     exactly and the surname is rare, but the obituary contains
#     nothing tying it to the case -- the same standard that refused
#     the Reidel and Montegut matches.
#   - Nancy Conde Rubio: OFAC states DOB 02 Sep 1972 but lists an
#     alternate DOB 19 Nov 1973 in the same entry -- two conflicting
#     stated dates is a factual conflict, so neither is written.
#
# RESEARCHED AND LEFT NULL (26 more names): Linas, Faulkner,
# Ferrand-Sapsis, Donald Martin, Gish, Dunbar, Bill Henry, Francisco
# Torres of the SF8, Gibson, Betts, Nowlin, Lane, Ronald T. Green,
# Clyne, Madden, Sabb, Darden, Dutton, Nasr, Podlesnik, George Allen,
# Chester Gallagher, Claxton, Coyne, Ann Morton, Mazzuchelli --
# sources state only ages or the person could not be traced.
#
# A record whose date is already set is left alone and reported --
# this script only fills blanks.
#
# Run from the repo root:
#   bash database/data/fix-birthdates-researched-8.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$rows = [
    ["slug" => "eugene-keyes",        "birth" => [1941, 10, 24], "death" => [2025, 5, 24], "src" => "Wikipedia, Gene Keyes"],
    ["slug" => "cortez-aaron-rice",   "birth" => [1989, 9, 21],  "death" => null,          "src" => "Wisconsin Circuit Court, Waukesha 2021CF001868"],
    ["slug" => "christopher-lay",     "birth" => [1985, 1, 21],  "death" => null,          "src" => "Oklahoma DOC record 515483"],
    ["slug" => "wil-casey-floyd",     "birth" => [1984, 12, 21], "death" => null,          "src" => "Wisconsin Circuit Court Access"],
    ["slug" => "chioke-auden-fugate", "birth" => [1999, 5, 29],  "death" => null,          "src" => "Asheville PD release via WNCT"],
    ["slug" => "jayma-abdoo",         "birth" => [1951],         "death" => [2006],        "src" => "Swarthmore Peace Collection finding aid / PBS POV"],
    ["slug" => "william-mackie",      "birth" => [1908],         "death" => null,          "src" => "Niukkanen v. McAlexander, 362 U.S. 390 dissent"],
    ["slug" => "sarah-tosi",          "birth" => null,           "death" => [2006, 4, 15], "src" => "Wellesley Magazine alumnae memorials"],
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
