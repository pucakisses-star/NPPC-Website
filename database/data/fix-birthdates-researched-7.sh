#!/usr/bin/env bash
#
# RESEARCHED BIRTHDATES, ROUND 7 -- seventh chunk: 36 names
# researched, 5 records get a date. Every date was found STATED in a
# source; nothing is derived from a press "age N".
#
# THE LEDGER (slug -- date -- source):
#
#   joanna-smith         b. 1970-01-29
#       Defense sentencing memorandum in U.S. v. Smith, D.D.C.
#       1:23-cr-00182 (the Degas / National Gallery climate case):
#       "was born in Berkeley, California on January 29, 1970".
#   bevelyn-beatty-williams b. 1991-03-15
#       Her own page (video titled "March 15, 1991 to Forever") and
#       IMDb bio: born March 15, 1991, Charlotte NC.
#   mohammad-el-mezain   b. 1953                       (year precision)
#       Freedom To Give (Holy Land Foundation family/support site):
#       born in Khan Yunis, Gaza in 1953.
#   monzer-al-kassar     b. 1945                       (year precision)
#       en.wikipedia.org/wiki/Monzer_al-Kassar infobox.
#   edward-murphy                       d. 2012-04-04
#       Obituary (webwire press release): Fr. Edward "Ned" Murphy SJ
#       died April 4, 2012 at Montefiore, Bronx. Birth date stays
#       null -- obituaries state only an age.
#
# RESEARCHED AND LEFT NULL (31 names): Barbara Carter, Reginald
# Thomas, Dashun Martin, James Kenneth Gluck, Hashem Younis Hashem
# Hnaihen, Frederick Urban, Thomas Welnicki, Therese Patricia Okoumou
# (Wikipedia itself flags her birth year missing), John Subleski,
# Peter James Yeager, Ryan David Lucero, Hazim Elashi, William
# Anderson, Raekwon Dac Blankenship, Shelby Ligons, Paul Douglas
# Revak, John J. Ford, Patrick Michael Gerola, Vinh Tan Nguyen,
# Christian Damian Cerno-Camacho, Amanda R. Wolf, Jacob D. Little,
# Hugh F. Farrell, Kyle Benjamin Douglas Calvert (Bhamwiki says only
# "born circa 1998" -- an age-derived estimate, refused), Jeffrey
# Richard Singer, Galen Sol Shireman-Grabowski, Gregory William Loel
# Timm, Mylene Vialard, Raphael Shaw, Steven James Murphy, Aida
# Yagmur Aston -- filings and coverage state only ages.
#
# A record whose date is already set is left alone and reported --
# this script only fills blanks.
#
# Run from the repo root:
#   bash database/data/fix-birthdates-researched-7.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$rows = [
    ["slug" => "joanna-smith",            "birth" => [1970, 1, 29], "death" => null,          "src" => "Defense sentencing memo, DDC 1:23-cr-00182"],
    ["slug" => "bevelyn-beatty-williams", "birth" => [1991, 3, 15], "death" => null,          "src" => "Her own page and IMDb bio"],
    ["slug" => "mohammad-el-mezain",      "birth" => [1953],        "death" => null,          "src" => "Freedom To Give, HLF support site"],
    ["slug" => "monzer-al-kassar",        "birth" => [1945],        "death" => null,          "src" => "Wikipedia, Monzer al-Kassar"],
    ["slug" => "edward-murphy",           "birth" => null,          "death" => [2012, 4, 4],  "src" => "Obituary, Fr. Ned Murphy SJ"],
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
