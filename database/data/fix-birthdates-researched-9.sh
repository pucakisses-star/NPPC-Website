#!/usr/bin/env bash
#
# RESEARCHED BIRTHDATES, ROUND 9 -- ninth chunk: 36 names researched,
# 5 records get a date. Every date was found STATED in a source;
# nothing is derived from a press "age N".
#
# THE LEDGER (slug -- date -- source):
#
#   lafi-khalil          b. 1974-10-24
#       DOJ Office of the Inspector General report "Bombs in
#       Brooklyn": born October 24, 1974 in Ajoul, Ramallah -- the
#       1997 subway-bomb case; media ages were inexact.
#   jose-maria-corredor-ibague b. 1966-12-17
#       OFAC SDN designation entry (a.k.a. Boyaco, FARC 1st Material
#       Support Network): DOB 17 Dec 1966, POB Santana, Boyaca --
#       one stated DOB, no alternates.
#   miriam-feingold      b. 1941-05-31
#       CRDL biographical entry states born 1941 in Brooklyn; the
#       Freedom Rides Museum birthday post states her birthday is
#       May 31 -- both stated, combined.
#   eugene-huelsman      b. 1963                       (year precision)
#       His CACD docket (2:21-mj-04866): "defendants Year of Birth:
#       1963". A conflicting IMDb year was not used -- the court
#       record in his own case outranks it.
#   nicholas-riddell                    d. 2014-09-17
#       Milwaukee Journal Sentinel obituary for Fr. Nicholas J.
#       Riddell, the Chicago 15 Carmelite. Birth stays null -- the
#       obituary states only an age.
#
# RESEARCHED AND LEFT NULL (31 names): Hummel, Tornow, Pridgen,
# Huber, Lujan, Felix Singer, Drechsler, Parzybok, Bryn Taylor (a
# data-broker birth month was refused), Hardy, Ramos, Allan Hoffman,
# Dial, Yach, Mahdawi, Barbara A. Thomas, Mulholland, Alvarado,
# William Goodman, Salamanca, Baghdadi, Ian Wallace, Linda Faith
# Greene, Milo Billman, Jesse William Waters, Perez Castro, Kimber,
# Nathan Pope, Frank Augusto, Bracamonte, Beltran Herrera -- filings
# and coverage state only ages, or the person is too obscure to
# trace.
#
# A record whose date is already set is left alone and reported --
# this script only fills blanks.
#
# Run from the repo root:
#   bash database/data/fix-birthdates-researched-9.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$rows = [
    ["slug" => "lafi-khalil",                "birth" => [1974, 10, 24], "death" => null,          "src" => "DOJ OIG report, Bombs in Brooklyn"],
    ["slug" => "jose-maria-corredor-ibague", "birth" => [1966, 12, 17], "death" => null,          "src" => "OFAC SDN designation entry"],
    ["slug" => "miriam-feingold",            "birth" => [1941, 5, 31],  "death" => null,          "src" => "CRDL / Freedom Rides Museum"],
    ["slug" => "eugene-huelsman",            "birth" => [1963],         "death" => null,          "src" => "CACD docket 2:21-mj-04866, Year of Birth"],
    ["slug" => "nicholas-riddell",           "birth" => null,           "death" => [2014, 9, 17], "src" => "Milwaukee Journal Sentinel obituary"],
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
