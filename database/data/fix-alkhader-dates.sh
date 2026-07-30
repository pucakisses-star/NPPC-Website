#!/usr/bin/env bash
#
# MUFID FAWAZ ALKHADER -- the record understates his imprisonment by
# fourteen months, because the case row carries the wrong arrest date.
#
# WHAT IS WRONG. The bio says he “was indicted February 4, 2025”, and the
# case row records the arrest and the start of incarceration as
# 2025-02-03. Both are wrong, and they are wrong in the same way: they
# take a date from the END of the case and treat it as the beginning.
#
#   February 4, 2025 was his GUILTY PLEA, not an indictment. He pleaded
#   guilty that day in Albany federal court before Judge Anne M.
#   Nardacci.
#
#   The offence, and his arrest, were on DECEMBER 7, 2023 -- fourteen
#   months earlier. At about 2 p.m. that day he fired a Kel-Tec KS7
#   12-gauge shotgun twice into the air outside Temple Israel in Albany.
#   The US Attorney for the Northern District of New York announced the
#   charge the following day, describing him as “age 28, of Schenectady,
#   New York”.
#
# HE HAS BEEN INSIDE THE WHOLE TIME, which is what makes the wrong date
# consequential rather than cosmetic. At his first appearance on
# December 8, 2023 Judge Christian Hummel remanded him without bail as a
# flight risk and a danger to the community; his lawyer then waived the
# detention hearing, so he stayed in jail. There is no bail period to
# subtract. Custody runs continuously from the day of the shooting.
#
# So imprisoned_for_days is recomputed from 2023-12-07 rather than
# 2025-02-03. He is flagged in custody with no release date, so the
# counter runs to today: roughly 966 days at the end of July 2026,
# against the 542 the record shows now.
#
# THE BIRTH YEAR IS DERIVABLE, from two official ages on two known
# dates:
#
#   28 in the charging announcement of December 8, 2023
#        -> born after 1994-12-08, on or before 1995-12-08
#   29 at sentencing on August 12, 2025
#        -> born after 1995-08-12, on or before 1996-08-12
#
# The two windows intersect in a single stretch of one year: he was born
# between August 13 and December 8, 1995. The month cannot be narrowed
# further, so the birthdate is stored as 1995 at circa precision and
# displays as “c. 1995”. The age of 29 reported at the February 4, 2025
# plea is consistent with that window and does not narrow it.
#
# Guard against the recycled-age trap: the two ages come from separate
# official announcements twenty months apart and DIFFER by one, which is
# the signature of a real birthday between them rather than a figure
# copied forward.
#
# ALSO RECORDED, all from the Justice Department announcements: the
# sentencing date of August 12, 2025; the judge; the guilty plea; and the
# five years of supervised release that follow the 120-month term, which
# the sentence field omitted.
#
# NOT SET: the institution. The record already carries inmate number
# 90377-510 and the address 2225 Haley Barbour Pkwy, Yazoo City,
# Mississippi, which is the federal correctional complex there -- but
# that address covers a low, a medium and a penitentiary, and nothing
# found says which of them holds him. Naming one would be a guess, so
# the address stands on its own.
#
# The payload uses curly apostrophes and curly quotation marks
# throughout, so it contains no straight quotes and is safe inside a
# single-quoted shell argument.
#
# Guarded: the birthdate is only written if the field is empty, and each
# case field only when it differs. A second run reports nothing to do.
#
# Run from the repo root:
#   bash database/data/fix-alkhader-dates.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "mufid-fawaz-alkhader")->with("cases")->first();

if (! $p) {
    echo "NOT FOUND: mufid-fawaz-alkhader — nothing changed.\n";
    return;
}

$bio = "Mufid Fawaz Alkhader, a United States citizen born in Iraq and living in Schenectady, New York, was 28 when he fired a Kel-Tec KS7 12-gauge shotgun twice into the air outside Temple Israel in Albany at about 2 p.m. on December 7, 2023, shouting “Free Palestine”. He tried to fire a third round, but the shotgun jammed; he then tried to tear an Israeli flag from a flagpole in front of the building. Adults and children were inside and nobody was hurt, but the synagogue called off the Chanukah concert and candle-lighting ceremony it had planned for that evening, and congregants were afraid to return. He said he had wanted to frighten Zionists. A restraining order already barred him from buying a gun himself, so the shotgun was straw-purchased on his behalf on November 5, 2023.\n\nHe was arrested the same day as the shooting and charged by criminal complaint with unlawfully possessing a firearm, being prohibited from having one as an unlawful user of a controlled substance. At his first appearance on December 8, 2023 he was remanded without bail as a flight risk and a danger to the community, and his lawyer subsequently waived the detention hearing, so he has been in continuous federal custody since the day of the offence.\n\nHe pleaded guilty on February 4, 2025 to conspiring to straw-purchase a firearm, to obstructing the free exercise of religious beliefs with a dangerous weapon, and to brandishing a firearm in furtherance of a crime of violence. On August 12, 2025 Judge Anne M. Nardacci sentenced him to 120 months in federal prison, to be followed by five years of supervised release.";

$before = $p->cases->first();
$beforeDays = $before ? $before->imprisoned_for_days : null;

$notes = [];

if ($p->description !== $bio) {
    $p->description = $bio;
    $notes[] = "bio rewritten (indictment date claim removed)";
}

if (! $p->birthdate) {
    // 28 on 2023-12-08 and 29 on 2025-08-12 intersect at Aug 13 - Dec 8, 1995.
    $p->setPartialDate("birthdate", 1995, null, null, true);
    $notes[] = "birthdate ".$p->formatPartialDate("birthdate");
}

if ($notes) {
    $p->save();
}

echo "  prisoner: ", ($notes ? implode("; ", $notes) : "already correct"), "\n";

$case = $p->cases->first();

if (! $case) {
    echo "  NO CASE ROW — dates not corrected.\n";
} else {
    $caseNotes = [];

    if (! $case->arrest_date || $case->arrest_date->format("Y-m-d") !== "2023-12-07") {
        $case->setPartialDate("arrest_date", 2023, 12, 7);
        $caseNotes[] = "arrest -> 2023-12-07";
    }

    if (! $case->incarceration_date || $case->incarceration_date->format("Y-m-d") !== "2023-12-07") {
        $case->setPartialDate("incarceration_date", 2023, 12, 7);
        $caseNotes[] = "incarceration -> 2023-12-07";
    }

    if (! $case->sentenced_date) {
        $case->setPartialDate("sentenced_date", 2025, 8, 12);
        $caseNotes[] = "sentenced 2025-08-12";
    }

    if (! $case->plead) {
        $case->plead = "Guilty";
        $caseNotes[] = "plead Guilty";
    }

    if (! $case->judge) {
        $case->judge = "Anne M. Nardacci";
        $caseNotes[] = "judge Anne M. Nardacci";
    }

    $sentence = "120 months in federal prison followed by five years of supervised release, imposed on August 12, 2025 by Judge Anne M. Nardacci of the Northern District of New York. He pleaded guilty on February 4, 2025 to conspiring to straw-purchase a firearm, obstructing the free exercise of religious beliefs with a dangerous weapon, and brandishing a firearm in furtherance of a crime of violence.";

    if ($case->sentence !== $sentence) {
        $case->sentence = $sentence;
        $caseNotes[] = "sentence text expanded";
    }

    if ($caseNotes) {
        $case->save();
    }

    echo "  case:     ", ($caseNotes ? implode("; ", $caseNotes) : "already correct"), "\n";
    echo "  imprisoned_for_days: ", ($beforeDays === null ? "null" : $beforeDays),
         " -> ", ($case->imprisoned_for_days === null ? "null" : $case->imprisoned_for_days), "\n";
}

$p->refresh();
echo "  age now: ", ($p->age === null ? "null" : $p->age),
     "  birthdate: ", $p->formatPartialDate("birthdate"),
     "  in_custody: ", (int) $p->in_custody, "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
