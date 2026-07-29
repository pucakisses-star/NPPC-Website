#!/usr/bin/env bash
#
# DERIVED BIRTH YEARS, ROUND 1 -- 36 names, 34 get a year.
#
# This is the method that replaces the retired bulk age-strip. The
# difference is not that ages are off limits; it is that every age here
# is paired with THE DATE IT WAS REPORTED AS-OF, by a researcher who
# read the source. An age of N on date D means a birth in
# [D-(N+1)y+1d, D-Ny]. Where two dated ages exist the windows are
# intersected, which often pins the calendar year outright.
#
# WHY THE BULK VERSION COULD NOT DO THIS. Measured against the records
# whose real dates are now known, taking the bio age and subtracting it
# from the first case date is right 7 times in 27. The wrecks are not
# off-by-ones: one case row holds a 1918 date for a 1978 case, one bio
# yields 73 for a man of 23, one bio age is from sentencing while the
# case date is the arrest. The arithmetic was never the problem. The
# pairing was.
#
# HOW THE THREE PRECISIONS ARE USED:
#
#   year   -- two dated ages intersect inside one calendar year, so the
#             year is pinned. Eight records.
#   month  -- a source published month and year outright. One record.
#   circa  -- the window straddles two calendar years, so the likelier
#             one is recorded and DISPLAYS AS "c. 1996". Twenty-five
#             records. This is honest: it says what is known.
#
# ------------------------------------------------------------------
# PINNED BY TRIANGULATION (stored as plain years)
#
#   mufid-fawaz-alkhader   1995  28 on 2023-12-08 (USAO-NDNY) and 29 on
#                                2025-08-12 (sentencing) -> Aug 13-Dec 8
#   salem-seleiman         1995  28 on 2024-06-27 and 30 on 2025-12-04
#                                (Manhattan DA) -> late Jun-Dec 4
#   yaakub-ira-vijandre    1987  38 on 2025-10-21 (legal team) and 38 on
#                                2025-12-13 (Atlanta Press Collective)
#   leo-a-randle           2004  19 in May 2024 and 20 on 2024-10-28.
#                                The researcher checked the outlet
#                                refreshes ages rather than recycling
#                                them: a co-defendant goes 26 -> 27
#                                across the same two stories.
#   jeffrey-stevens        1982  41 on 2024-02-20 and 42 on 2024-07-17
#   jenny-oconnell-nowain  1984  41 on 2025-06-27 and 41 on 2026-01-28
#   karrem-nasr            2000  23 on 2023-12-29 and 24 on 2025-01-27
#                                (both SDNY releases)
#   zaid-mohammed-mahdawi  1998  26 on 2024-10-04 and 26 on 2025-04-04
#
# MONTH AND YEAR PUBLISHED OUTRIGHT
#
#   luke-harper       1995-10  The Atlanta Police arrestee list for the
#                              March 5, 2023 raid publishes month/year
#                              of birth, not age: "Harper, Luke |
#                              10/1995 | FL". Not derived at all.
#
# WINDOW STRADDLES TWO YEARS (stored circa, shown "c. YYYY")
#
#   aditya-aswani            1996 (or 1997)  29 on 2026-03-16 and 2026-04-21
#   dean-wyrzykowski         1996 (or 1997)  same two Ridglan reports
#   gabriela-saldana         2002 (or 2003)  23 on 2026-04-17
#   aurelio-luis-perez-lugones 1964 (or 1965) 61 on 2026-01-22 (DOJ)
#   izhar-khan               1986 (or 1987)  24 on 2011-05-14, 26 on 2013-01-17
#   julio-cesar-irungaray    1969 (or 1970)  56 on 2026-06-25
#   spencer-anderson         2001 (or 2002)  24 on 2026-02-26
#   daniel-jongyon-park      1992 (or 1993)  32 on 2025-06-04 and at death
#   mohammed-hoque           2004 (or 2005)  20 at 2025-03-28 arrest
#   xaelyn-dunbar            2006 (or 2005)  19 on 2025-09-24
#   chase-grijalva           1995 (or 1996)  28 on 2024-06-12, 30 at 2026 sentencing
#   christopher-k-zelle      1986 (or 1987)  37 on 2024-04-29, 38 on 2025-08-15
#   george-a-vassilatos      1999 (or 1998)  25 twice in 2024, 26 by Jun 2025
#   csaba-john-csukas        1984 (or 1985)  39 on 2024-03-13; 1984 is the
#                                            only year consistent with the
#                                            age read as-of either the
#                                            release or the offence
#   elijah-michael-lane      1994 (or 1995)  29 on the 2024-05-02 PSU list
#   will-parzybok            2000 (or 2001)  23 on the same list
#   hashem-younis-hashem-hnaihen 1980 (or 1981) 43 on 2024-08-15, 44 at sentencing
#   hicham-talal             1999 (or 2000)  24 on 2024-02-26
#   jarrid-bailey-huber      1999 (or 2000)  23 on 2023-05-02, 25 on 2025-01-15
#   terrence-clyne           1955 (or 1956)  68 on 2024-01-04 and 2024-08-08
#   arieon-robinson          2001 (or 2000)  SOURCES CONFLICT: GBI says 22 and
#                                            the AJC says 21 within the same
#                                            December 2022. The August 2023
#                                            indictment age is compatible with
#                                            either, and both branches land in
#                                            2000 or 2001, which is exactly what
#                                            circa asserts. Do not treat 2001
#                                            as settled.
#   leonard-vioselle         2002 (or 2001)  20, 20, then 21; only Dec 29-31
#                                            2001 keeps 2001 alive
#   nicholas-olson           1997 (or 1996)  25, 25, then 26; same narrow sliver
#   serena-hertel            1997 (or 1996)  25, 25, then 26
#   julia-dupuis             1999 (or 1998)  24 on 2023-05-02, 24 at the
#                                            indictment, 26 on 2025-05-12.
#                                            A near coin flip -- 117 days of
#                                            the window in 1998, 122 in 1999.
#
# TWO LEFT NULL, BOTH DELIBERATELY:
#
#   graham-ball       No dated source even names him as a Boeing Five
#                     defendant; coverage withholds the names. The age in
#                     his bio is unverifiable.
#   christian-damian-cerno-camacho  THE RECORD CONFLATES TWO PEOPLE. The
#                     name is Cerna-Camacho, 28, of Boyle Heights, who
#                     punched a CBP officer. The bio facts -- 20-year-old
#                     Walmart employee, Pico Rivera, bystander video
#                     contradicting the assault claim -- are Adrian Andrew
#                     Martinez. Either age would pin one man{39}s date to
#                     the other man{39}s case. It needs splitting first.
#
# A hostile personal-targeting site carrying one subject{39}s details was
# refused, as were mugshot aggregators.
#
# Only fills blanks: a record that already has a birthdate is reported
# and left alone.
#
# Run from the repo root:
#   bash database/data/fix-birthyears-derived-1.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

// [slug, year, month|null, approximate]
$rows = [
    // Pinned by triangulation -> plain year precision.
    ["mufid-fawaz-alkhader", 1995, null, false],
    ["salem-seleiman", 1995, null, false],
    ["yaakub-ira-vijandre", 1987, null, false],
    ["leo-a-randle", 2004, null, false],
    ["jeffrey-stevens", 1982, null, false],
    ["jenny-oconnell-nowain", 1984, null, false],
    ["karrem-nasr", 2000, null, false],
    ["zaid-mohammed-mahdawi", 1998, null, false],

    // Month and year published outright -> month precision.
    ["luke-harper", 1995, 10, false],

    // Window straddles two calendar years -> circa, shown "c. YYYY".
    ["aditya-aswani", 1996, null, true],
    ["dean-wyrzykowski", 1996, null, true],
    ["gabriela-saldana", 2002, null, true],
    ["aurelio-luis-perez-lugones", 1964, null, true],
    ["izhar-khan", 1986, null, true],
    ["julio-cesar-irungaray", 1969, null, true],
    ["spencer-anderson", 2001, null, true],
    ["daniel-jongyon-park", 1992, null, true],
    ["mohammed-hoque", 2004, null, true],
    ["xaelyn-dunbar", 2006, null, true],
    ["chase-grijalva", 1995, null, true],
    ["christopher-k-zelle", 1986, null, true],
    ["george-a-vassilatos", 1999, null, true],
    ["csaba-john-csukas", 1984, null, true],
    ["elijah-michael-lane", 1994, null, true],
    ["will-parzybok", 2000, null, true],
    ["hashem-younis-hashem-hnaihen", 1980, null, true],
    ["hicham-talal", 1999, null, true],
    ["jarrid-bailey-huber", 1999, null, true],
    ["terrence-clyne", 1955, null, true],
    ["arieon-robinson", 2001, null, true],
    ["leonard-vioselle", 2002, null, true],
    ["nicholas-olson", 1997, null, true],
    ["serena-hertel", 1997, null, true],
    ["julia-dupuis", 1999, null, true],
];

$set = 0;
$kept = 0;
$missing = 0;
$byPrecision = [];

foreach ($rows as [$slug, $year, $month, $approximate]) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) {
        echo "  NOT FOUND: {$slug}\n";
        $missing++;
        continue;
    }

    if ($p->birthdate) {
        echo "  {$slug}: birthdate already set ({$p->formatPartialDate("birthdate")}) — left alone\n";
        $kept++;
        continue;
    }

    $p->setPartialDate("birthdate", $year, $month, null, $approximate);
    $p->save();

    $prec = $p->datePrecisionFor("birthdate");
    $byPrecision[$prec] = ($byPrecision[$prec] ?? 0) + 1;
    $set++;
    echo "  ", str_pad($slug, 32), " ", str_pad($p->formatPartialDate("birthdate"), 10),
         " [", $prec, "]", ($p->age !== null ? "  age now ".$p->age : ""), "\n";
}

echo "\nBirth years written: {$set}\n";
foreach ($byPrecision as $prec => $n) {
    echo "  {$prec}: {$n}\n";
}
echo "Left alone (already had a date): {$kept}\n";
echo "Slugs not found: {$missing}\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
