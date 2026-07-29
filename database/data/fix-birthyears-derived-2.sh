#!/usr/bin/env bash
#
# DERIVED BIRTH YEARS, ROUND 2 -- 36 names, 31 dated, 5 of them with a
# full day-precision date.
#
# Same method as round 1: every age is paired with the date it was
# reported as-of, and where two dated ages exist the birth windows are
# intersected. Two refinements earned their place this round.
#
# THE RECYCLED-AGE GUARD. Outlets reprint stale ages constantly, and a
# reprint masquerading as a second observation would produce a
# confidently wrong "pinned" year. Researchers had to prove
# independence before intersecting, usually by checking whether a
# CO-DEFENDANT{39}s age moved between the same two stories. It repeatedly
# mattered:
#   - the Fulton RICO indictment list is real (Carroll 22->23, Grim
#     48->49 against the January release), so it can be intersected;
#   - Patch bumped Rahman but left Mattis unchanged in 2023, so that
#     figure was discarded in favour of Reuters;
#   - the Riverside arraignment list re-derived ages (Castro 22->23
#     while three co-defendants held steady), which is exactly what
#     PINS Castro to 1999;
#   - a January 2023 MyNewsLA story reprints all six Riverside ages
#     verbatim, so it was excluded as a third observation;
#   - San Diego DA releases proved partly stale -- one co-defendant{39}s
#     age DECREASED over six months, another set was mechanically +2 --
#     so Yach was left at circa rather than pinned.
#
# THE MISPARSE PROBLEM, MEASURED. The age our own bio text carries was
# wrong for at least eight of these 36. Five of them -- Grim, and the
# four Akron arrestees -- carried the age of the person who was KILLED
# (Tortuguita, 26; Jayland Walker, 25) rather than the age of the
# person arrested. MacLean, Kautz and Patterson also read 26. A regex
# would have written eight plausible-looking, entirely false years.
# This is the single strongest argument for reading sources by hand.
#
# ------------------------------------------------------------------
# STATED FULL DATES (day precision -- no derivation at all)
#
#   priscilla-grim           1974-03-06  The Indypendent: arrested the
#       night of March 5, 2023 and "turned 49 the following day while
#       she was being held in booking". Four later ages all fall inside
#       the resulting window.
#   alexandria-ty-fite       1990-04-25  |  Riverside County District
#   elise-saramarielle-kelder 1994-01-20 |  Attorney news release on the
#   kamile-dincsoy           1973-11-29  |  Historic Courthouse case
#   oliver-edu-solares-herrera 1998-07-10| publishes defendants{39} DOBs
#       outright. An arrest list printing Fite as 22 is contradicted by
#       the DA{39}s DOB and by every court-stage report, and was refused.
#
# PINNED BY TRIANGULATION (plain year precision)
#
#   francis-carroll   2000  22 (Dec 2022), 22 (Jan 2023), 23 (Aug 2023)
#                           -> Jan 22-Aug 29 2000; a 2018 high-school
#                           graduation agrees
#   peter-karasev     1986  36 (Mar 2023), 36 (Nov 2023), 38 (Apr 2025),
#                           39 (Dec 2025) -> Nov 10-Dec 16 1986. The
#                           36->38 jump itself requires birthdays in
#                           late 2023 AND late 2024. A lone "35" from
#                           one outlet is irreconcilable with three DOJ
#                           releases and was discarded.
#   alexander-jacob-castro 1999  22 at booking, 23 at arraignment ->
#                           Jul 31-Oct 6 1999. The discrepancy that
#                           looks like sloppiness is what pins it.
#   chester-gallagher 1949  73 (DOJ, Oct 2022) against 43 (St Petersburg
#                           Times, Apr 1993) -- 29 years apart, so
#                           recycling is impossible. Same distinctive
#                           man: the Las Vegas officer fired in 1989 for
#                           joining a clinic blockade in uniform.
#   colinford-mattis  1987  32 (NPR, Jul 2020), 34 (Patch, at the Oct
#                           2021 plea) -> Jul 2-Oct 20 1987
#   gamaly-hollis     1972  51 (Apr 2024), 52 (Nov 14 2024), 53 (Apr
#                           2026) -> Apr 30-Nov 14 1972. A lone "53" a
#                           week after the "52" is an error, 3 to 1.
#   deep-alpesh-kumar-patel 2002  21 (Nov 2023), 21 (Mar 2024 sentencing)
#   jeffrey-scott-hobgood   1959  64 (Oct 2023 arrest reporting), 64
#                           (USAO, May 2024). The office{39}s own charging
#                           release carried NO age, so the later figure
#                           provably was not copied from it.
#
# WINDOW STRADDLES TWO YEARS (circa -- displays "c. YYYY")
#
#   emily-murphy 1985, graham-evatt 2002, henri-feola 2000,
#   nadja-geier 1998, david-chavez 1995, edwin-pena 1997,
#   fernando-lopez 1979, stephanie-amesquita 1990,
#   vanessa-carrasco 1983, adele-maclean 1981, marlon-kautz 1983,
#   savannah-patterson 1993, bryn-taylor 1996,
#   orlando-javier-ramirez 1993, erich-louis-yach 1984,
#   kyle-wagner 1988, aida-yagmur-aston 1990, anthony-ladd 1977
#
#   Notes worth keeping: Kautz triangulates across THIRTEEN years, via a
#   2011 AJC story about his Copwatch settlement that SaportaReport ties
#   to him. Patterson finally has a legitimate source -- the earlier
#   refusal stood because the only date circulating was on a hostile
#   tracking site; the derived window happens to contain it without
#   relying on it. Feola is flagged: a 2018 school and 2022 college
#   graduation suggest the August age may be a year stale. Taylor is
#   flagged the other way: data brokers put her in January 1997, the
#   other branch, so the ambiguity is real.
#
# LEFT NULL (5)
#
#   wendy-lujan   IRRECONCILABLE. The sheriff{39}s release says 40 at the
#       December 2023 raid; a CALO News feature narrating that same raid
#       says 37. Three years apart, beyond what circa can honestly
#       absorb, and no third source breaks the tie. Her identity is
#       certain; only her age is disputed.
#   hashim-ali, brendan-eaton, damean-martin, devonna-culver
#       Akron July 2022 arrestees. Martin and Culver are confirmed as
#       named plaintiffs in the settled federal suit, but no legitimate
#       outlet publishes an age for any of them -- the arraignment
#       coverage gives only an aggregate "18 to 36" range. Their
#       personal details appear only on mugshot aggregators and data
#       brokers, which this project does not use.
#
# Only fills blanks: a record that already has a birthdate is reported
# and left alone.
#
# Run from the repo root:
#   bash database/data/fix-birthyears-derived-2.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

// [slug, year, month|null, day|null, approximate]
$rows = [
    // Stated full dates.
    ["priscilla-grim", 1974, 3, 6, false],
    ["alexandria-ty-fite", 1990, 4, 25, false],
    ["elise-saramarielle-kelder", 1994, 1, 20, false],
    ["kamile-dincsoy", 1973, 11, 29, false],
    ["oliver-edu-solares-herrera", 1998, 7, 10, false],

    // Pinned by triangulation -> plain year.
    ["francis-carroll", 2000, null, null, false],
    ["peter-karasev", 1986, null, null, false],
    ["alexander-jacob-castro", 1999, null, null, false],
    ["chester-gallagher", 1949, null, null, false],
    ["colinford-mattis", 1987, null, null, false],
    ["gamaly-hollis", 1972, null, null, false],
    ["deep-alpesh-kumar-patel", 2002, null, null, false],
    ["jeffrey-scott-hobgood", 1959, null, null, false],

    // Window straddles two years -> circa.
    ["emily-murphy", 1985, null, null, true],
    ["graham-evatt", 2002, null, null, true],
    ["henri-feola", 2000, null, null, true],
    ["nadja-geier", 1998, null, null, true],
    ["david-chavez", 1995, null, null, true],
    ["edwin-pena", 1997, null, null, true],
    ["fernando-lopez", 1979, null, null, true],
    ["stephanie-amesquita", 1990, null, null, true],
    ["vanessa-carrasco", 1983, null, null, true],
    ["adele-maclean", 1981, null, null, true],
    ["marlon-kautz", 1983, null, null, true],
    ["savannah-patterson", 1993, null, null, true],
    ["bryn-taylor", 1996, null, null, true],
    ["orlando-javier-ramirez", 1993, null, null, true],
    ["erich-louis-yach", 1984, null, null, true],
    ["kyle-wagner", 1988, null, null, true],
    ["aida-yagmur-aston", 1990, null, null, true],
    ["anthony-ladd", 1977, null, null, true],
];

$set = 0;
$kept = 0;
$missing = 0;
$byPrecision = [];

foreach ($rows as [$slug, $year, $month, $day, $approximate]) {
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

    $p->setPartialDate("birthdate", $year, $month, $day, $approximate);
    $p->save();

    $prec = $p->datePrecisionFor("birthdate");
    $byPrecision[$prec] = ($byPrecision[$prec] ?? 0) + 1;
    $set++;
    echo "  ", str_pad($slug, 30), " ", str_pad($p->formatPartialDate("birthdate"), 14),
         " [", $prec, "]", ($p->age !== null ? "  age ".$p->age : ""), "\n";
}

echo "\nBirth dates written: {$set}\n";
ksort($byPrecision);
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
