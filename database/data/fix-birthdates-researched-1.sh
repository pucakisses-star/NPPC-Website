#!/usr/bin/env bash
#
# RESEARCHED BIRTHDATES, ROUND 1 -- the first chunk of the individual
# research the bio-age revert made necessary. Every date below was found
# STATED in a source; none is derived from an "age N" mention. Names
# researched in this round whose sources state only an age stay null.
#
# THE LEDGER (slug -- date -- source):
#
#   louis-lingg          b. 1864-09-09  d. 1887-11-10
#       en.wikipedia.org/wiki/Louis_Lingg -- Haymarket defendant, born
#       Mannheim; died by suicide the eve of the execution.
#   wesley-robert-wells  b. 1909        d. 1976        (year precision)
#       Souls, Winter 2000 (columbia.edu/cu/ccbh/souls/vol2no1/
#       vol2num1art3.pdf) states his fortieth birthday fell in 1949;
#       death in early 1976 per the SFSU Bay Area TV Archive
#       (diva.sfsu.edu/collections/sfbatv/bundles/217361).
#   victor-puertas       b. 1977-11-12
#       Unicorn Riot, Nov 2023: "November 12 marked Victor Puertas
#       46th birthday" -- a stated birthday while jailed, not an age.
#   shamim-mafi          b. 1981                       (year precision)
#       U.S.A. v. Mafi, C.D. Cal. 2:26-cr-00266, case summary on
#       CourtListener states "Year of Birth 1981".
#   jamison-wagner       b. 1984                       (year precision)
#       U.S. v. Jamison R. Wagner, D.N.M. 1:25-mj-00729, criminal
#       complaint caption states "YEAR OF BIRTH 1984".
#   merle-africa         b. 1951        d. 1998-03-13
#       Jericho Movement profile "Merle Africa 1951-1998"; death date
#       March 13, 1998 per onamove.com/move-9/merle-africa.
#   debbie-africa        b. 1956-08-04
#       Prisoner Solidarity profile of Debbie Sims Africa (MOVE 9),
#       born August 4, 1956.
#
# RESEARCHED AND LEFT NULL (no source states a date): Steve Kelly SJ
# (only "c. 1949"), Andrew Lawrence, Marlon Kautz, Priscilla Grim,
# Adele MacLean, Gabriela Saldana, Aurelio Luis Perez Lugones, Salah
# Sarsour, Susan Marie Parker, Heather Doyle, and the four in group 3.
# Savannah Patterson is EXCLUDED on sourcing ethics: the only page
# stating a birth month is a hostile tracking site, which this project
# does not use for activists personal data.
#
# A record whose birthdate is already set is left alone and reported --
# this script only fills blanks.
#
# Run from the repo root:
#   bash database/data/fix-birthdates-researched-1.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$rows = [
    ["slug" => "louis-lingg",         "birth" => [1864, 9, 9],   "death" => [1887, 11, 10], "src" => "Wikipedia"],
    ["slug" => "wesley-robert-wells", "birth" => [1909],         "death" => [1976],         "src" => "Souls Winter 2000 / SFSU archive"],
    ["slug" => "victor-puertas",      "birth" => [1977, 11, 12], "death" => null,           "src" => "Unicorn Riot Nov 2023"],
    ["slug" => "shamim-mafi",         "birth" => [1981],         "death" => null,           "src" => "CACD 2:26-cr-00266 filing"],
    ["slug" => "jamison-wagner",      "birth" => [1984],         "death" => null,           "src" => "DNM 1:25-mj-00729 complaint"],
    ["slug" => "merle-africa",        "birth" => [1951],         "death" => [1998, 3, 13],  "src" => "Jericho Movement / onamove.com"],
    ["slug" => "debbie-africa",       "birth" => [1956, 8, 4],   "death" => null,           "src" => "Prisoner Solidarity profile"],
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
        echo "  {$row["slug"]}: birth {$p->formatPartialDate("birthdate")}"
            .($p->death_date ? ", death {$p->formatPartialDate("death_date")}" : "")
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
