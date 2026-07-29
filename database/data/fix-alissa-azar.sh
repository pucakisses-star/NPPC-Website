#!/usr/bin/env bash
#
# Alissa Azar -- corrected custody dates and date of birth.
#
# BIRTHDATE: February 12, 1991, supplied by the site owner as 2/12/91
# and read in the American month/day/year order -- she is an Oregon
# activist and every other date on this record is written that way. Day
# precision. The record previously had no birthdate and no age at all;
# the age now derives from it instead of being absent.
#
# The case carried an incarceration date of August 1, 2024 and no
# release date at all. August 1 is not a custody date: it is a stand-in
# for the month of the jury verdict, which the bio gives only as
# "August 2024". With no release date behind it, the imprisonment
# counter read zero for a woman who actually served the whole term.
#
#   Sep  9, 2024   entered the Clackamas County Jail
#   Sep 23, 2024   released, approximately
#                  = 14 days
#
# THE FOURTEEN DAYS CORROBORATE THEMSELVES. The sentence imposed was
# fourteen days in jail plus thirty-six months of supervised probation
# with GPS monitoring, and September 9 to September 23 is exactly
# fourteen days. That agreement is why the approximate release date is
# recorded at day precision rather than being softened to the month --
# but the case text says plainly that the release date is approximate,
# so nobody later reads it as documented to the day.
#
# NOTHING ELSE IS TOUCHED. The separate open Multnomah County matter
# over the May 2024 pro-Palestine occupation at Portland State
# University produced no custody on this record and gets no case row;
# the hung count on attempted use of tear gas stays described as it was.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-alissa-azar.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "alissa-azar")->with("cases")->first();
if (! $p) {
    echo "NOT FOUND: alissa-azar\n";
    exit(1);
}

$p->in_custody = false;
$p->released = true;
$p->awaiting_trial = false;
$p->setPartialDate("birthdate", 1991, 2, 12);
$p->save();

$case = $p->cases->first() ?? $p->cases()->create([]);
$case->sentence = "Fourteen days in jail, thirty-six months of supervised probation and GPS monitoring, following the August 2024 jury verdict. She entered the Clackamas County Jail on September 9, 2024 and was released about September 23, 2024 — fourteen days, the full term. The release date is approximate; it is recorded to the day because September 9 plus the fourteen days of the sentence lands exactly there, but no source fixes it more precisely than approximately. The August 1, 2024 incarceration date this record previously carried was a stand-in for the month of the verdict, not a date in custody.";
$case->setPartialDate("incarceration_date", 2024, 9, 9);
$case->setPartialDate("release_date", 2024, 9, 23);
$case->save();

$p->refresh()->load("cases");
$case = $p->cases->first();
echo "Alissa Azar  [{$p->slug}]\n";
echo "  born ".($p->formatPartialDate("birthdate") ?: "-")."   age ".($p->age ?? "-")."   (expect Feb 12, 1991)\n";
echo "  incarcerated ".$case->incarceration_date->toDateString()."   released ".$case->release_date->toDateString()."\n";
echo "  imprisoned_for_days = ".($case->imprisoned_for_days ?? "null")."  (expect 14, was 0)\n";
echo "  years in prison: ".implode(", ", $p->years_in_prison ?: [])."\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
