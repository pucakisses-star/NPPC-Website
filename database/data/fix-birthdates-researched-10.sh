#!/usr/bin/env bash
#
# RESEARCHED BIRTHDATES, ROUND 10 -- tenth chunk: 36 names researched,
# 9 records get a date. Every date was found STATED in a source;
# nothing is derived from a press "age N".
#
# THE LEDGER (slug -- date -- source):
#
#   john-hinshaw         b. 1954-12-27
#       Defense sentencing mitigation report in U.S. v. Hinshaw
#       (D.D.C., Surgi-Clinic FACE case): "born on December 27, 1954,
#       in Queens, New York".
#   duncan-andrew-small  b. 1992-05-18
#       FBI criminal complaint, U.S. v. Small (M.D. Fla.
#       8:26-mj-01534), which recounts his July 4, 2022 Vance
#       Monument arrest -- identity confirmed by the document itself.
#   alex-jason-hall      b. 1988-05-05
#       Denver ABC prisoner-support page for the Utah mink/AETA case:
#       "born on May 5, 1988 in Oklahoma City".
#   brenda-travis        b. 1945-03-16  d. 2026-05-17
#       en.wikipedia.org/wiki/Brenda_Travis -- the McComb Greyhound
#       sit-in activist; Mississippi Today reported her death.
#   carolyn-long         b. 1940-10-30  d. 2023-04-12
#       This record is Carolyn Long Banks of the 1960 Magnolia Room
#       sit-in; en.wikipedia.org/wiki/Carolyn_Long_Banks, consistent
#       with the AJC obituary.
#   armando-gomez        b. 1948-05-01
#       Colombian Supreme Court extradition ruling (quoted by Asuntos
#       Legales): "nacio el primero de mayo de 1948".
#   lisa-leggio          b. 1978-03-27  d. 2019-03 (month precision)
#       Dignity Memorial obituary (Holland, MI) states the birth
#       date; the page conflicts internally on the death day (March
#       15 vs 25, 2019), so the death is recorded at month precision.
#       MICATS memorials published March 26, 2019 confirm identity.
#   wilmer-young         b. 1887        d. 1983-09
#       Swarthmore Friends Historical Library finding aid: born 1887
#       in Linn County, Iowa; died September 1983. Year and month
#       precision respectively.
#   tearra-naasia-guthrie               d. 2022-05-13
#       Gun Memorial and the North Area funeral home obituary for
#       Tearra Guthrie Smalls, matching the record note that she
#       died May 2022. Birth stays null.
#
# EXCLUDED ON IDENTITY GROUNDS: an Edward Zabo Find a Grave record
# fits the age but nothing ties it to the radium case; a Carlos J.
# Bayon obituary does not match the Grand Island defendant.
#
# RESEARCHED AND LEFT NULL (25 more names): Sanks, Smith-Birge, Chase
# A. Davis, Keppler, Talal, Jaber, Hernandez, Hunt, Habib, Ladd,
# Macauley, Hekima Ana, Zelle, Ross Anderson, Viglakis, Colcleasure
# (tracking-site date refused), Toa, Ibarguen-Palacio,
# Solares-Herrera, Polites, Terry Sullivan (sources conflict on his
# age), Vicci Hamlin, Christian Rea, Rosenbloom, Starks.
#
# A record whose date is already set is left alone and reported --
# this script only fills blanks.
#
# Run from the repo root:
#   bash database/data/fix-birthdates-researched-10.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$rows = [
    ["slug" => "john-hinshaw",          "birth" => [1954, 12, 27], "death" => null,           "src" => "Defense mitigation report, DDC Surgi-Clinic case"],
    ["slug" => "duncan-andrew-small",   "birth" => [1992, 5, 18],  "death" => null,           "src" => "FBI complaint, MD Fla 8:26-mj-01534"],
    ["slug" => "alex-jason-hall",       "birth" => [1988, 5, 5],   "death" => null,           "src" => "Denver ABC prisoner-support page"],
    ["slug" => "brenda-travis",         "birth" => [1945, 3, 16],  "death" => [2026, 5, 17],  "src" => "Wikipedia / Mississippi Today"],
    ["slug" => "carolyn-long",          "birth" => [1940, 10, 30], "death" => [2023, 4, 12],  "src" => "Wikipedia, Carolyn Long Banks"],
    ["slug" => "armando-gomez",         "birth" => [1948, 5, 1],   "death" => null,           "src" => "Colombian Supreme Court extradition ruling"],
    ["slug" => "lisa-leggio",           "birth" => [1978, 3, 27],  "death" => [2019, 3],      "src" => "Dignity Memorial obituary / MICATS memorials"],
    ["slug" => "wilmer-young",          "birth" => [1887],         "death" => [1983, 9],      "src" => "Swarthmore Friends Historical Library"],
    ["slug" => "tearra-naasia-guthrie", "birth" => null,           "death" => [2022, 5, 13],  "src" => "Gun Memorial / North Area funeral home"],
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
