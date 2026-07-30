#!/usr/bin/env bash
#
# THE TEN RECORDS FLAGGED BOTH IN CUSTODY AND RELEASED.
#
# These are the contradictions left over from the filter question. Unlike
# the 82-row imprisoned_or_exiled desync — which was a stale derivation
# and safe to repair in bulk — every one of these needed looking up,
# because the flags are contradictory in the source data and there is no
# rule that says which half is wrong. Researched individually, they came
# back as FOUR different states, which is the plainest possible answer to
# whether in_custody and released are redundant: they are not, and two of
# these people can only be described correctly as neither.
#
# THREE ARE STILL INSIDE. released was the wrong half.
#
#   fred-burton          A live Pennsylvania DOC locator query returns
#                        inmate AF3896, FREDERICK BURTON, DOB 12/15/1946,
#                        committed from Philadelphia, at SCI Chester —
#                        56 years served. His stored birthdate already
#                        reads 1946-12-15, which the DOC record confirms
#                        exactly.
#   alvaro-hernandez     TDCJ #00255735 returns HERNANDEZ, ALVARO JR,
#                        age 74, Estelle unit, projected release LIFE
#                        SENTENCE. His two case rows are NOT an error:
#                        the 1991-03-14 release ends the 1976 Brewster
#                        County conviction, and the 1997-06-08
#                        incarceration is the separate current sentence.
#                        Only the record-level released flag was wrong.
#   haki-malik-abdullah  A Jericho Movement post dated June 28, 2026
#                        places Michael Green, C-56123, at Folsom and
#                        asks supporters to back his commutation — an
#                        affirmative statement that he is inside, not
#                        merely an absence of release news.
#
# FIVE ARE OUT. in_custody was the wrong half.
#
#   aline-espinosa-villegas  BOP shows release 04/01/2025 from FMC
#                        Carswell; handed to ICE the same day and since
#                        deported to Chile. Her case row already carries
#                        2025-04-01.
#   andrew-augustyniak-duncan  BOP shows release 07/14/2025 from USP
#                        McCreary, and the PA DOC locator returns nothing
#                        for him, so he is not in state custody either.
#                        His case row had NO release date; it gets one.
#   david-mckay          BOP shows release 04/06/2012. The stored case
#                        date reads 2012-04-05, one day earlier; it is
#                        left as it stands rather than churned over a
#                        single day, but the BOP figure is noted here.
#   joseph-remiro        Reported in 2024 to have been quietly paroled in
#                        2018, the last imprisoned SLA member. ONLY THE
#                        YEAR IS SOURCED — the stored 2018-06-14 day
#                        could not be confirmed anywhere, and no official
#                        CDCR record was reachable. Left alone, flagged
#                        here.
#   marshall-conway      Marshall "Eddie" Conway, released March 4, 2014
#                        after 43 years and 11 months, then a producer at
#                        The Real News. His case rows had no release
#                        date; one gets it.
#
# TWO DIED IN CUSTODY AND WERE NEVER RELEASED. Both flags are false and a
# death date goes on instead — the state a single boolean could not
# express, and the reason the pair is worth keeping.
#
#   luis-v-rodriguez     Died of natural causes April 14, 2016, aged 60,
#                        while an inmate of R.J. Donovan, serving life
#                        for the 1978 shooting of two CHP officers.
#   romaine-fitzgerald   Romaine "Chip" Fitzgerald, repeatedly denied
#                        parole, died behind bars March 29, 2021 aged 71
#                        after more than 51 years — the longest-held
#                        Black Panther Party prisoner. His stored
#                        birthdate 1949-04-11 matches his memorials.
#
# AND ONE DIED AFTER RELEASE, which is a fourth combination again:
# Conway died February 13, 2023 of complications of pneumonia at the
# Long Beach VA hospital, nearly nine years out.
#
# ONE THING FLAGGED, NOT CHANGED: Luis Rodriguez has a stored birthdate
# of 1956-04-14 and now a death date of 2016-04-14 — the same month and
# day. He was reported as 60 when he died, so it is arithmetically
# possible he died on his sixtieth birthday, but an exact month-day match
# is also the signature of a death date mistakenly copied into a birth
# field. His memorial gives only "1956-2016". Worth a look; not touched
# here.
#
# ALSO NOT SET: death_in_custody_date on the specific case rows for
# Rodriguez and Fitzgerald. That field is the more precise home for a
# death inside, but they have three and two case rows respectively and
# choosing the operative one would be guesswork. The prisoner-level
# death date is enough for the day counter, which falls back to it.
#
# imprisoned_or_exiled is derived on save, so the three custody
# corrections propagate to the public active lists automatically.
#
# Guarded: flags are only written when they differ, dates only when
# empty. A second run reports nothing to do.
#
# Run from the repo root:
#   bash database/data/fix-custody-flag-conflicts.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

// slug, in_custody, released, death [Y,M,D] or null, case release date or null
$rows = [
    ["fred-burton", true, false, null, null],
    ["alvaro-hernandez", true, false, null, null],
    ["haki-malik-abdullah", true, false, null, null],

    ["aline-espinosa-villegas", false, true, null, null],
    ["andrew-augustyniak-duncan", false, true, null, "2025-07-14"],
    ["david-mckay", false, true, null, null],
    ["joseph-remiro", false, true, null, null],
    ["marshall-conway", false, true, [2023, 2, 13], "2014-03-04"],

    ["luis-v-rodriguez", false, false, [2016, 4, 14], null],
    ["romaine-fitzgerald", false, false, [2021, 3, 29], null],
];

$flagFixes = 0;
$deaths = 0;
$caseDates = 0;
$missing = 0;

foreach ($rows as [$slug, $inCustody, $released, $death, $caseRelease]) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->with("cases")->first();
    if (! $p) {
        echo "  NOT FOUND: {$slug}\n";
        $missing++;
        continue;
    }

    $notes = [];

    if ((bool) $p->in_custody !== $inCustody || (bool) $p->released !== $released) {
        $p->in_custody = $inCustody;
        $p->released = $released;
        $notes[] = "flags -> in_custody=".($inCustody ? "yes" : "no").", released=".($released ? "yes" : "no");
        $flagFixes++;
    }

    if ($death && ! $p->death_date) {
        $p->setPartialDate("death_date", $death[0], $death[1], $death[2]);
        $notes[] = "death ".$p->formatPartialDate("death_date");
        $deaths++;
    }

    if ($notes) {
        $p->save();
    }

    if ($caseRelease) {
        // first() takes a callback; firstWhere() expects a key, so it would
        // silently fail to match here.
        $case = $p->cases->first(fn ($c) => ! $c->release_date) ?? $p->cases->first();
        if ($case && ! $case->release_date) {
            $case->release_date = $caseRelease;
            $case->save();
            $notes[] = "case release ".$caseRelease." (".$case->imprisoned_for_days." days)";
            $caseDates++;
        }
    }

    echo "  ", str_pad($slug, 27), " ", ($notes ? implode("; ", $notes) : "already correct"),
         "   [active=", (int) $p->imprisoned_or_exiled, "]\n";
}

echo "\nFlag contradictions resolved: {$flagFixes}\n";
echo "Death dates recorded:        {$deaths}\n";
echo "Case release dates added:    {$caseDates}\n";
echo "Slugs not found:             {$missing}\n";

$left = Prisoner::withoutGlobalScopes()->where("in_custody", true)->where("released", true)->count();
echo "Records still flagged BOTH in custody and released: {$left}\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
