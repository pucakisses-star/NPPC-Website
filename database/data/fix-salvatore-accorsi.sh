#!/usr/bin/env bash
#
# SALVATORE ACCORSI -- the record stopped at "facing the electric chair"
# and never said he was acquitted.
#
# He already existed as salvatore-accorsi, with a year-only arrest date,
# no custody dates, no life dates, and a verdict field reading
# "Extradited and held for trial, 1929". A reader came away thinking he
# may well have been convicted of killing a state trooper. He was tried
# and found NOT GUILTY.
#
# WHAT THIS SETS
#
#   life dates      1897-09-23 to 1945-02-19
#   arrest          1929-06-12, upgraded from a bare "1929" to the day
#   incarceration   1929-06-12 (arrested on Staten Island and held from
#                   that day, through New York custody and extradition,
#                   in the Allegheny County jail at Pittsburgh)
#   release         1929-12-13, the day of the acquittal
#   verdict         No -- acquitted
#   charges         "Murder." The narrative that was sitting in the
#                   charges field moves into the description, where it
#                   belongs; charges name the offence.
#
# THE DAY COUNTER WILL READ 184, not 185. June 12 to December 13, 1929
# is 184 days as an interval and 185 counting both end days. 185 is the
# figure in the sources; the counter measures intervals, as it does for
# every other record, so it is left to do that consistently rather than
# special-cased here.
#
# THE DESCRIPTION is rewritten to carry the whole arc: the 22 August
# 1927 Sacco-Vanzetti protest at Guido Grove near Cheswick and the
# mounted state police charge in which Private Downey was shot; the 1929
# arrest and extradition; the International Labor Defense campaign that
# also supported his wife and three young children; the trial from 9
# December; and the acquittal.
#
# DISPUTED EVIDENCE IS ATTRIBUTED, NOT ASSERTED. A contemporary defense
# account reported that a trooper who identified Accorsi at trial had
# earlier said he could not describe the man who fired, and that another
# description gave the gunman a moustache; labor publications of the day
# disagreed about whether Accorsi was at the protest at all or at home
# miles away. Those are recorded as what those sources said. The word
# "framed" that the old description used in its own voice becomes the
# ILD{39}s characterisation, which is what it was -- the jury{39}s verdict is
# the fact.
#
# Fellow Cheswick defendants Steve Kurepa and Tony De Bernardini are
# already in the database and date the raid the same way, so the
# accounts agree. Neither Downey record in the database is the trooper:
# axel-downey is an Everett Massacre IWW prisoner and john-e-downey a
# First World War objector.
#
# Guarded: life dates and custody dates only fill if empty; the
# description and charges are only rewritten while the old text is still
# there, so a second run reports nothing to do.
#
# Run from the repo root:
#   bash database/data/fix-salvatore-accorsi.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "salvatore-accorsi")->with("cases")->first();
if (! $p) {
    echo "NOT FOUND: salvatore-accorsi\n";
    exit(1);
}

$changed = [];

if (! $p->birthdate) {
    $p->setPartialDate("birthdate", 1897, 9, 23);
    $changed[] = "birth 1897-09-23";
}
if (! $p->death_date) {
    $p->setPartialDate("death_date", 1945, 2, 19);
    $changed[] = "death 1945-02-19";
}

$desc = "Salvatore Accorsi was an Italian immigrant tried for the murder of a Pennsylvania state trooper and acquitted after roughly six months in custody facing the electric chair.\n\nOn August 22, 1927, thousands of workers — many of them striking Italian coal miners — gathered at Guido Grove near Cheswick, Pennsylvania to protest the impending executions of Nicola Sacco and Bartolomeo Vanzetti. Mounted state police charged into the gathering, using clubs, horses and tear gas to disperse the crowd. During the confrontation Private Downey was shot and killed.\n\nAccorsi subsequently moved to Staten Island, New York. He was arrested there on June 12, 1929 and extradited to Pennsylvania, where he was confined in the Allegheny County jail at Pittsburgh while facing a possible death sentence. The International Labor Defense, which called the case a frame-up, organized a national campaign on his behalf and supported his wife and three young children during his imprisonment.\n\nHis murder trial began in Pittsburgh on December 9, 1929. The prosecution relied heavily on disputed eyewitness identifications. A contemporary defense account reported that a state trooper who positively identified Accorsi at trial had previously testified that he could not describe the person who fired the shot, and that another description characterized the gunman as having a moustache. Contemporary labor publications also presented conflicting accounts of whether Accorsi had attended the protest or was at home several miles away when the shooting occurred.\n\nAfter deliberating for approximately eighteen hours, the jury found Accorsi not guilty on December 13, 1929, and he was released after approximately 185 calendar days in custody. The novelist Sinclair Lewis attended the trial and spoke at a public celebration following the acquittal.";

if (str_contains((string) $p->description, "facing the electric chair, and defended by the ILD")) {
    $p->description = $desc;
    $changed[] = "description rewritten (now records the acquittal)";
}

if ($changed) {
    $p->save();
}
echo "Prisoner: ", ($changed ? implode("; ", $changed) : "nothing to change"), "\n";
echo "  born ", (string) ($p->formatPartialDate("birthdate") ?? "—"),
     "   died ", (string) ($p->formatPartialDate("death_date") ?? "—"),
     "   age ", var_export($p->age, true), "\n";

$case = $p->cases->first();
if (! $case) {
    echo "NO CASE ROW — expected one.\n";
    exit(1);
}

$caseChanged = [];

// Upgrade the bare year to the documented day.
if ($case->datePrecisionFor("arrest_date") !== "day") {
    $case->setPartialDate("arrest_date", 1929, 6, 12);
    $caseChanged[] = "arrest upgraded to 1929-06-12";
}
if (! $case->incarceration_date) {
    $case->incarceration_date = "1929-06-12";
    $caseChanged[] = "incarcerated 1929-06-12";
}
if (! $case->release_date) {
    $case->release_date = "1929-12-13";
    $caseChanged[] = "released 1929-12-13";
}
if (trim((string) $case->convicted) !== "No — acquitted") {
    $case->convicted = "No — acquitted";
    $caseChanged[] = "verdict -> No — acquitted";
}
if (str_contains((string) $case->charges, "Framed for the murder")) {
    $case->charges = "Murder.";
    $caseChanged[] = "charges -> Murder.";
}

$note = "Tried in Pittsburgh from December 9, 1929 and acquitted on December 13, 1929 after the jury deliberated about eighteen hours.";
if (! str_contains((string) $case->sentence, "acquitted on December 13")) {
    $case->sentence = trim(($case->sentence ? rtrim($case->sentence)."\n\n" : "").$note);
    $caseChanged[] = "sentence note appended";
}

if ($caseChanged) {
    $case->save();
}

echo "Case: ", ($caseChanged ? implode("; ", $caseChanged) : "nothing to change"), "\n";
echo "  arrest ", (string) ($case->formatPartialDate("arrest_date") ?? "—"),
     "   custody ", (string) $case->incarceration_date, " to ", (string) $case->release_date,
     "   days = ", var_export($case->imprisoned_for_days, true), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
