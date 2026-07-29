#!/usr/bin/env bash
#
# RESEARCHED BIRTHDATES, ROUND 5 -- fifth chunk: 36 names researched,
# 9 records get a date. Every date was found STATED in a source;
# nothing is derived from an "age N" mention in press coverage.
#
# THE LEDGER (slug -- date -- source):
#
#   alex-poindexter      b. 1944-11-01  d. 2023-12-07
#       This record is Ed(ward) Poindexter of the Omaha Two.
#       en.wikipedia.org/wiki/Rice-Poindexter_case; died in the
#       Nebraska State Penitentiary; Omaha World-Herald corroborates.
#   agnes-bauerlein      b. 1928-02-12  d. 2015-11-26
#       Oshkosh Northwestern obituary (legacy.com): born Feb 12, 1928
#       in Nijmegen, Holland; Philadelphia Inquirer obit confirms the
#       Plowshares activist identity.
#   david-michael-ansberry b. 1952-02-21
#       Federal criminal complaint, Nederland bombing case (D. Colo.
#       16-cr-00341, on CourtListener): "date of birth 02/21/1952".
#   briana-boston        b. 1982-04-20
#       Polk County Jail booking record for her Dec 2024 arrest lists
#       DOB 4/20/1982. The live page renders empty since release, so
#       this rests on the indexed record -- spot-check if in doubt.
#   stephen-rowland      b. 1947                       (year precision)
#       UC Berkeley oral history "Randy Rowland: A Life of Resistance
#       and The Presidio 27" (digicoll.lib.berkeley.edu/record/219398):
#       born 1947 in St. Louis.
#   james-mcfarlane      b. 1751        d. 1794-07-17  (birth: year)
#       Find a Grave memorial for Major James McFarlane of the
#       Whiskey Rebellion. CAVEAT: the tombstone states he died July
#       17, 1794 "aged 43", so the 1751 birth year is the
#       inscription-stated age against an exact death date -- standard
#       for 18th-century figures, but it is the tombstone speaking.
#   daniel-jongyon-park                 d. 2025-06-24
#       CNN and DOJ: died in custody at MDC Los Angeles.
#   james-edward-garrett                d. 1978-01-21
#       The Black Panther v18 no.7 (the source his record already
#       cites); no independent source located.
#   chester-jackson                     d. 1988        (year precision)
#       The Advocate (Angola 3 coverage): died in prison in 1988.
#
# RESEARCHED AND LEFT NULL (27 names): the Presidio 27 soldiers
# (Pulley, Wilkins, Seals, Sales, Breidert), the 1968 Oakland shootout
# Panthers (Lankford, Scott), Charles Wyche, Earl Leverette, Donald
# Zepeda, Christopher McIntosh, Mufid Fawaz Alkhader, Natalie Hanna
# White, David Butler, Samuel Sanchez, Joseph Ybarra, Michael Sykes,
# Fr. Joseph E. Mulligan, Elliott White-Haskins, Gilbert Montegut,
# Antonio Quintana, Frank Shuford, Ola Mae Davis, Joshua Cartrette,
# Ginovanni Brumbelow, Floyd Lee Corkins II, Cal Ray Biggins --
# sources state only ages, dockets are redacted, or identity could
# not be confirmed (a Montegut obituary fits the age but nothing ties
# it to the Angola case).
#
# A record whose date is already set is left alone and reported --
# this script only fills blanks.
#
# Run from the repo root:
#   bash database/data/fix-birthdates-researched-5.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$rows = [
    ["slug" => "alex-poindexter",        "birth" => [1944, 11, 1], "death" => [2023, 12, 7],  "src" => "Wikipedia, Rice-Poindexter case (Ed Poindexter, Omaha Two)"],
    ["slug" => "agnes-bauerlein",        "birth" => [1928, 2, 12], "death" => [2015, 11, 26], "src" => "Oshkosh Northwestern obituary"],
    ["slug" => "david-michael-ansberry", "birth" => [1952, 2, 21], "death" => null,           "src" => "Federal complaint, D. Colo. 16-cr-00341"],
    ["slug" => "briana-boston",          "birth" => [1982, 4, 20], "death" => null,           "src" => "Polk County Jail booking record"],
    ["slug" => "stephen-rowland",        "birth" => [1947],        "death" => null,           "src" => "UC Berkeley oral history, Presidio 27"],
    ["slug" => "james-mcfarlane",        "birth" => [1751],        "death" => [1794, 7, 17],  "src" => "Find a Grave memorial, tombstone inscription"],
    ["slug" => "daniel-jongyon-park",    "birth" => null,          "death" => [2025, 6, 24],  "src" => "CNN / DOJ"],
    ["slug" => "james-edward-garrett",   "birth" => null,          "death" => [1978, 1, 21],  "src" => "The Black Panther v18 no.7"],
    ["slug" => "chester-jackson",        "birth" => null,          "death" => [1988],         "src" => "The Advocate, Angola 3 coverage"],
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
