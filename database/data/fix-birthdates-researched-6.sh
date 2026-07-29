#!/usr/bin/env bash
#
# RESEARCHED BIRTHDATES, ROUND 6 -- sixth chunk: 36 names researched,
# 12 records get a date, the best hit rate so far. Every date was
# found STATED in a source; nothing is derived from a press "age N".
#
# THE LEDGER (slug -- date -- source):
#
#   pascale-cecile-veronique-ferrier b. 1967-05-08
#       Her own sworn statement in the D.D.C. plea-hearing transcript
#       (1:23-cr-00028, Doc 4): "I was born in France on May 8th,
#       1967." The ricin-letters defendant.
#   terrence-johnson    b. 1963-02-27  d. 1997-02-27
#       Wikipedia (Prince Georges County PD article) and WaPo: he
#       died by suicide on his 34th birthday.
#   irving-david-rubin  b. 1945-04-12  d. 2002-11-13
#       en.wikipedia.org/wiki/Irv_Rubin -- JDL chairman, born
#       Montreal; died after the MDC Los Angeles suicide attempt.
#   twymon-ford-myers   b. 1950-11-27  d. 1973-11-14
#       en.wikipedia.org/wiki/Twymon_Myers -- BLA member, died in
#       the Bronx shootout.
#   barry-bondhus       b. 1945-09-30  d. 2018-12-12
#       WikiTree profile matched to the Big Lake draft-board case via
#       his father Tom Bondhus; the obituary text confirms the death
#       date at Maple Lake.
#   anni-rainbow        b. 1949-06-22  d. 2022-06-24
#       Menwith Hill Accountability Campaign newsletter, Summer 2022:
#       died 24 June 2022 "two days after her 73rd birthday" -- a
#       stated birthday; Guardian obituary confirms aged 73.
#   bruce-dancis        b. 1948-04-14
#       Library of Congress name authority record (no00093126) for
#       the author of Resister, the Cornell SDS draft resister.
#   james-e-robinson    b. 1985-06-27
#       FBI complaint affidavit, U.S. v. Robinson, N.D. Ohio
#       5:18-mj-01099: "date of birth 06/27/1985".
#   william-f-kruse     b. 1894        d. 1979        (year precision)
#       en.wikipedia.org/wiki/William_F._Kruse -- the YPSL director
#       in the Chicago Espionage Act case.
#   mourad-topalian     b. 1943                       (year precision)
#       en.wikipedia.org/wiki/Mourad_Topalian.
#   michael-cullen      b. 1941                       (year precision)
#       Marquette University archives biographical note on the
#       Milwaukee 14 / Casa Maria founder: born in Ireland in 1941.
#   van-l-mayes         b. 1987                       (year precision)
#       Federal complaint in U.S. v. Mayes, E.D. Wis. 18-CR-154,
#       states "DOB: xx/xx/1987" with month and day redacted.
#
# RESEARCHED AND LEFT NULL (24 names): Freddie Morrow, Brennon
# Nastacio, Rose Jackson, Josie Robotin, Darwin Lance Brown, Amber
# Smith-Stewart, Chisom Kingston, Tony Alexander Hamilton, Carlos
# Gamarra-Murillo, Nancy Epling, Mickael Gedlu, Meredith Lowell, Jon
# Bayless, Jerome Singleton, George Washington Henson, Salem
# Seleiman, Peter Fordi, Jeffrey Stevens, Fr. John Pietra, Csaba John
# Csukas, Bradley Neil Crowder, Hunter Mattin, Peter Karasev, Khalid
# Awan -- filings and coverage state only ages, or identity could not
# be confirmed (a 1914-born Fordi obituary is the wrong age for the
# Camden 28 defendant, so it was refused).
#
# A record whose date is already set is left alone and reported --
# this script only fills blanks.
#
# Run from the repo root:
#   bash database/data/fix-birthdates-researched-6.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$rows = [
    ["slug" => "pascale-cecile-veronique-ferrier", "birth" => [1967, 5, 8],   "death" => null,           "src" => "Her sworn statement, DDC plea transcript"],
    ["slug" => "terrence-johnson",                 "birth" => [1963, 2, 27],  "death" => [1997, 2, 27],  "src" => "Wikipedia / Washington Post"],
    ["slug" => "irving-david-rubin",               "birth" => [1945, 4, 12],  "death" => [2002, 11, 13], "src" => "Wikipedia, Irv Rubin"],
    ["slug" => "twymon-ford-myers",                "birth" => [1950, 11, 27], "death" => [1973, 11, 14], "src" => "Wikipedia, Twymon Myers"],
    ["slug" => "barry-bondhus",                    "birth" => [1945, 9, 30],  "death" => [2018, 12, 12], "src" => "WikiTree profile + obituary"],
    ["slug" => "anni-rainbow",                     "birth" => [1949, 6, 22],  "death" => [2022, 6, 24],  "src" => "MHAC newsletter Summer 2022 / Guardian obituary"],
    ["slug" => "bruce-dancis",                     "birth" => [1948, 4, 14],  "death" => null,           "src" => "Library of Congress authority record"],
    ["slug" => "james-e-robinson",                 "birth" => [1985, 6, 27],  "death" => null,           "src" => "FBI complaint affidavit, ND Ohio 5:18-mj-01099"],
    ["slug" => "william-f-kruse",                  "birth" => [1894],         "death" => [1979],         "src" => "Wikipedia, William F. Kruse"],
    ["slug" => "mourad-topalian",                  "birth" => [1943],         "death" => null,           "src" => "Wikipedia, Mourad Topalian"],
    ["slug" => "michael-cullen",                   "birth" => [1941],         "death" => null,           "src" => "Marquette University archives"],
    ["slug" => "van-l-mayes",                      "birth" => [1987],         "death" => null,           "src" => "Federal complaint, ED Wis 18-CR-154 (redacted to year)"],
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
