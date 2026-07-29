#!/usr/bin/env bash
#
# RESEARCHED BIRTHDATES, ROUND 3 -- third chunk of the individual
# research: 36 names researched, 11 records get a date. Every date was
# found STATED in a source; nothing is derived from an "age N" mention.
#
# THE LEDGER (slug -- date -- source):
#
#   john-artis           b. 1946-10-15  d. 2021-11-07
#       NYT obituary (via centurion.org): "John Arnold Artis was born
#       on Oct. 15, 1946, in Portsmouth, Va."; died Nov 7, 2021.
#   frank-dukes          b. 1930-11-27  d. 2023-11-11
#       bhamwiki.com/w/Frank_Dukes; consistent with the Birmingham
#       Times obituary (died Veterans Day, days shy of 93).
#   father-michael-doyle b. 1934-11-03  d. 2022-11-04
#       Healey Funeral Homes obituary: born Nov 3, 1934, Rossduff,
#       Co. Longford; died Nov 4, 2022 (day after his 88th birthday).
#   warren-wells         b. 1947-11-13  d. 2001-06-29
#       sixties-l memorial post, July 2001: born Nov 13, 1947 in the
#       Alice Griffith projects, SF; died June 29 in CDC custody.
#   calla-walsh          b. 2004-06-09
#       en.wikipedia.org/wiki/Calla_Walsh, citing a Boston Magazine
#       profile.
#   david-mckay          b. 1986                       (year precision)
#       en.wikipedia.org/wiki/David_McKay_(activist): born in
#       Midland, Texas in 1986.
#   joan-bell            b. 1948                       (year precision)
#       Joan Elizabeth Andrews biography (cited by Wikidata):
#       1948, Lewisburg, Tenn.
#   farouk-abdel-muhti   b. 1947        d. 2004-07-21  (birth: year)
#       revolutionarydemocracy.org obituary: born 1947 in Ramallah,
#       died July 21, 2004 in Philadelphia; Democracy Now agrees.
#   tommy-lee-hines                     d. 2020-02-11
#       Decatur Daily: died Feb. 11, 2020. Birth date stays null --
#       only an age at his 1978 arrest is on record.
#   anne-sheppard-turner                d. 2011        (year precision)
#       Wikipedia, Wilmington Ten: Ann Shepard died in 2011.
#   bruce-washington                    d. 1974-10-26
#       The Black Panther newspaper (as quoted in his record bio):
#       shot and killed Oct 26, 1974.
#
# RESEARCHED AND LEFT NULL (25 names): the Atlanta forest-defender
# RICO defendants (Carroll, Evatt, Feola, Geier, Vioselle, Robinson,
# Hertel, Olson, Dupuis), Sophie Ross and Bridget Shergalis of the
# Merrimack Four (an actress shares the Shergalis name and her DOB
# conflicts with the reported age, so it is refused), Ellen Reiche,
# Edward Schinzing, Robert Hoopes, Marc Aisen, David Agranoff, Clare
# Grady (Wikipedia itself tags her birth year missing), Hafiz Muhammad
# Sher Ali Khan, Mohammed Hoque, Milton Eugene Lindsey, Arthur Turco,
# James Reynolds Mendoza, Joyce Guerrero, James K. Holley, Leonard
# Vioselle -- sources state only ages, or identity could not be
# confirmed.
#
# A record whose date is already set is left alone and reported --
# this script only fills blanks.
#
# Run from the repo root:
#   bash database/data/fix-birthdates-researched-3.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$rows = [
    ["slug" => "john-artis",           "birth" => [1946, 10, 15], "death" => [2021, 11, 7],  "src" => "NYT obituary via centurion.org"],
    ["slug" => "frank-dukes",          "birth" => [1930, 11, 27], "death" => [2023, 11, 11], "src" => "Bhamwiki / Birmingham Times obituary"],
    ["slug" => "father-michael-doyle", "birth" => [1934, 11, 3],  "death" => [2022, 11, 4],  "src" => "Healey Funeral Homes obituary"],
    ["slug" => "warren-wells",         "birth" => [1947, 11, 13], "death" => [2001, 6, 29],  "src" => "sixties-l memorial, July 2001"],
    ["slug" => "calla-walsh",          "birth" => [2004, 6, 9],   "death" => null,           "src" => "Wikipedia, citing Boston Magazine"],
    ["slug" => "david-mckay",          "birth" => [1986],         "death" => null,           "src" => "Wikipedia"],
    ["slug" => "joan-bell",            "birth" => [1948],         "death" => null,           "src" => "Joan Elizabeth Andrews biography via Wikidata"],
    ["slug" => "farouk-abdel-muhti",   "birth" => [1947],         "death" => [2004, 7, 21],  "src" => "revolutionarydemocracy.org obituary"],
    ["slug" => "tommy-lee-hines",      "birth" => null,           "death" => [2020, 2, 11],  "src" => "Decatur Daily"],
    ["slug" => "anne-sheppard-turner", "birth" => null,           "death" => [2011],         "src" => "Wikipedia, Wilmington Ten"],
    ["slug" => "bruce-washington",     "birth" => null,           "death" => [1974, 10, 26], "src" => "The Black Panther newspaper, via record bio"],
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
