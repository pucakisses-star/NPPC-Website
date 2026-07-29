#!/usr/bin/env bash
#
# WESLEY SOMERS -- both life dates on this record were wrong, and one of
# them said a living man was dead.
#
# The record was carrying birthdate 2001-05-09 and death date
# 1975-07-31: a death twenty-six years BEFORE the birth. It surfaced
# because the age accessor used an absolute date diff, so the impossible
# pair rendered as a plausible age instead of showing that something was
# broken. (That accessor now computes signed and suppresses the
# impossible result -- a separate change.)
#
# WHAT THE SOURCES SAY. He is the Wesley Somers of Hendersonville,
# Tennessee who pleaded guilty to federal arson for the May 30, 2020
# fire at the Metro/Historic Courthouse in Nashville during the George
# Floyd protests, and was sentenced on March 23, 2022 to five years
# followed by three years of supervised release (U.S. Attorney, Middle
# District of Tennessee).
#
#   BIRTHDATE 1995-02-16. The Davidson County Criminal Court Clerk
#   record for the Wesley Somers charged with aggravated arson,
#   vandalism and aggravated riot on 5/30/2020 -- the same case -- gives
#   that date of birth, and it agrees with the age 25 reported at the
#   arrest. This is a court record for the case itself, not a
#   people-search listing.
#
#   DEATH DATE: CLEARED. No source reports any death. He was a living
#   federal defendant through the 2022 sentencing and nothing since
#   suggests otherwise. The stored 1975 date has no basis at all.
#
# TWO SMALLER CORRECTIONS to the description, both from the same DOJ
# sources and both done as exact string replacements so a re-run is a
# no-op:
#
#   - "in June 2020" -> "on May 30, 2020". The fire was set the night of
#     Saturday, May 30.
#   - "Wesley Somers, 25," -> "Wesley Somers". The age mention comes out
#     now that a researched birthdate stands behind it, which is the
#     rule for these bios: no age in the prose, and no birth year
#     invented from an age either.
#
# The sentenced date (2022-03-23) is filled in on the case, which had no
# dates at all. Nothing else about the case is touched: the custody
# flags and the day counter need an incarceration and release date that
# no source in hand gives, so they are left as they are rather than
# reconstructed.
#
# Run from the repo root:
#   bash database/data/fix-wesley-somers.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "wesley-somers")->with("cases")->first();
if (! $p) {
    echo "NOT FOUND: wesley-somers\n";
    exit(1);
}

echo "Before: birthdate=", var_export($p->birthdate ? (string) $p->birthdate->toDateString() : null, true),
     " death=", var_export($p->death_date ? (string) $p->death_date->toDateString() : null, true),
     " age=", var_export($p->age, true), "\n";

// Court-record birthdate, full day precision.
$p->setPartialDate("birthdate", 1995, 2, 16);

// No death: clear the fabricated date and its precision entry.
$p->setPartialDate("death_date", null);

$desc = (string) $p->description;
$desc = str_replace("Wesley Somers, 25, was charged", "Wesley Somers was charged", $desc);
$desc = str_replace("at the Nashville City Hall in June 2020", "at the Nashville City Hall on May 30, 2020", $desc);
$p->description = $desc;

$p->save();

echo "After:  birthdate=", (string) $p->birthdate->toDateString(),
     " death=", var_export($p->death_date, true),
     " age=", var_export($p->age, true), "\n";
echo "Description: ", $desc, "\n";

$case = $p->cases->first();
if ($case && ! $case->sentenced_date) {
    $case->sentenced_date = "2022-03-23";
    $case->save();
    echo "Case sentenced_date set to 2022-03-23.\n";
} elseif ($case) {
    echo "Case sentenced_date already set (", (string) $case->sentenced_date, ") — left alone.\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
