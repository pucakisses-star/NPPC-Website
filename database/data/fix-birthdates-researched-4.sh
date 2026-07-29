#!/usr/bin/env bash
#
# RESEARCHED BIRTHDATES, ROUND 4 -- fourth chunk: 36 names researched,
# 8 records get a date. Every date was found STATED in a source;
# nothing is derived from an "age N" mention.
#
# THE LEDGER (slug -- date -- source):
#
#   cameron-monte-smith  b. 1974-09-30
#       USA v. Smith, D.N.D. 1:23-cr-00118 suppression motion (on
#       CourtListener) quotes sheriff affidavits stating
#       "Cameron Smith (DOB 9/30/1974)" -- the Wheelock Substation case.
#   michelle-lunsky      b. 1993-01-14
#       Wisconsin Circuit Court Access, State v. Michelle D Lunsky,
#       Dane County 2026CF000900 (Ridglan Farms case) states the DOB.
#   joan-bird            b. 1949-03-09
#       1970 New York Times profile of the Panther 21 nursing student:
#       "she was born on March 9, 1949".
#   chase-iron-eyes      b. 1978-03-06
#       en.wikipedia.org/wiki/Chase_Iron_Eyes infobox; NoDAPL felony
#       incitement defendant confirmed.
#   collis-english       b. 1925        d. 1952-12-31  (birth: year)
#       Wikipedia, Trenton Six: (1925-1952), died in prison of a
#       heart attack on December 31, 1952.
#   regina-brave         b. 1941                       (year precision)
#       Warrior Women Project profile: "Regina Brave (1941 - )".
#   douglas-joshua-ellerman b. 1979                    (year precision)
#       SAGE Encyclopedia of Terrorism entry "Ellerman, Josh (1979-)";
#       the Sandy, Utah fur co-op bombing case.
#   mohammed-rafiq-butt                 d. 2001-10-23
#       WSWS/AFP: died Oct 23, 2001 in Hudson County jail. Birth date
#       stays null -- only "age 55" is on record.
#
# RESEARCHED AND LEFT NULL (28 names): the Glenville co-defendants
# (Leslie Jackson, John Hardrick, Lathan L. Donald), the Presidio 27
# soldiers (Gentile, Osczepinski, Sood, Reidel), the Carbondale 3
# (Leonard Thomas), Dane County 2026 defendants (Wyrzykowski, Aswani),
# SOA Watch line-crossers (Mickey, Lincoln), David J. Miller, Yvette
# Kelly, Wendell Wade, Charles Bursey, Irving Young, Charles
# Wakefield, Adam Blackwell, Mateen Abdul-Shaheed, Don Cotton, Aaron
# Fox, Cesar Aguirre, Julio Cesar Irungaray, Jackson George Green,
# Woodrow Wilson Gillis, Joshua Wollstein, Timothy Adams -- sources
# state only ages, or identity could not be confirmed. A Find a Grave
# candidate for Lawrence Reidel was refused: nothing on the memorial
# ties it to the Presidio case.
#
# A record whose date is already set is left alone and reported --
# this script only fills blanks.
#
# Run from the repo root:
#   bash database/data/fix-birthdates-researched-4.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$rows = [
    ["slug" => "cameron-monte-smith",     "birth" => [1974, 9, 30], "death" => null,           "src" => "USA v. Smith DND 1:23-cr-00118 filing"],
    ["slug" => "michelle-lunsky",         "birth" => [1993, 1, 14], "death" => null,           "src" => "Wisconsin Circuit Court, Dane Co. 2026CF000900"],
    ["slug" => "joan-bird",               "birth" => [1949, 3, 9],  "death" => null,           "src" => "1970 New York Times profile"],
    ["slug" => "chase-iron-eyes",         "birth" => [1978, 3, 6],  "death" => null,           "src" => "Wikipedia"],
    ["slug" => "collis-english",          "birth" => [1925],        "death" => [1952, 12, 31], "src" => "Wikipedia, Trenton Six"],
    ["slug" => "regina-brave",            "birth" => [1941],        "death" => null,           "src" => "Warrior Women Project"],
    ["slug" => "douglas-joshua-ellerman", "birth" => [1979],        "death" => null,           "src" => "SAGE Encyclopedia of Terrorism"],
    ["slug" => "mohammed-rafiq-butt",     "birth" => null,          "death" => [2001, 10, 23], "src" => "WSWS/AFP"],
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
