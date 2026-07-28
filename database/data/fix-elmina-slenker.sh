#!/usr/bin/env bash
#
# Elmina Drake Slenker -- corrected record.
#
# The record carried no birthdate, a year-precision death of 1908, a stored
# age of 81 and a case whose only date was a placeholder arrest of
# 1887-01-01, so the counter showed nothing at all for six months of jail.
#
# Corrected chronology:
#
#   Apr 28, 1887  arrested at her Snowville home under the federal Comstock
#                 postal-obscenity law; held on \$2,000 bail, principally at
#                 the Wytheville Jail -- custody runs from this day
#   Jul 12, 1887  indicted
#   Oct 31, 1887  trial opened at Abingdon and the guilty verdict came the
#                 same day
#   Nov  4, 1887  judgment arrested and Slenker released: the indictment had
#                 failed to allege that she knew the writings were legally
#                 obscene
#
#   190 days in custody -- about six months and one week.
#
# THE AGE. Born December 23, 1827 and died February 1, 1908, she was 80 at
# death, in her eighty-first year -- which is what contemporary references
# calling her 81 were counting. Setting both dates makes the model recompute
# the age to 80 on save and replaces the stored 81; no manual age is written.
#
# NAMES. Middle name Drake, and her birth name Elizabeth Drake is recorded as
# an AKA. The display name and slug stay "Elmina Slenker" so no links break.
#
# IDEOLOGIES. The record had none. Two existing taxonomy terms are added:
# Reproductive Rights (her birth-control advocacy -- the term Margaret Sanger
# and Ben Reitman carry) and Press Freedom (the prosecution was for what she
# wrote and mailed). There is no freethought or atheism term in the taxonomy,
# so that part of her identity stays in the bio rather than minting a
# one-record ideology.
#
# The bio is the supplied text with two typographical fixes only: a doubled
# space removed, and an em dash inserted where the sentence about the
# overturned conviction ran into its own explanation.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-elmina-slenker.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "elmina-slenker")->with("cases")->first();
if (! $p) {
    echo "NOT FOUND: elmina-slenker\n";
    exit(1);
}

$p->middle_name = "Drake";
$p->aka = "Elizabeth Drake";
$p->state = "Virginia";
$p->in_custody = false;
$p->released = true;
$p->awaiting_trial = false;
$p->setPartialDate("birthdate", 1827, 12, 23);
$p->setPartialDate("death_date", 1908, 2, 1);
$p->ideologies = ["Reproductive Rights", "Press Freedom"];
$p->description = "Elmina Slenker was a freethought writer, atheist and birth control advocate, in Snowville, Virginia. Postal investigators began correspondence with her using pseudonyms in order to obtain letters from her which could be used to charge her with obscenity. On April 28, 1887, she was arrested under the federal Comstock postal-obscenity law and held on \$2,000 bail, principally at the Wytheville Jail. Indicted on July 12, she went on trial in Abingdon on October 31 and was found guilty of mailing allegedly obscene writings. On November 4, 1887, the conviction was overturned — the indictment failed to allege that she knew the writings were legally obscene — and Slenker was released.";
$p->save();

$wytheville = Institution::firstOrCreate(
    ["name" => "Wytheville Jail"],
    ["city" => "Wytheville", "state" => "Virginia"],
);

$case = $p->cases->first() ?? $p->cases()->create([]);
$case->institution_id = $wytheville->id;
$case->charges = "Mailing allegedly obscene writings, under the federal Comstock postal-obscenity law — private letters written in reply to postal investigators who had opened a correspondence with her under pseudonyms for the purpose of obtaining them.";
$case->indicted = "July 12, 1887";
$case->convicted = "Found guilty on October 31, 1887 — VACATED on November 4, 1887, when the court arrested judgment because the indictment failed to allege that she knew the writings were legally obscene.";
$case->sentence = "None imposed. Held on \$2,000 bail from her arrest on April 28, 1887, principally at the Wytheville Jail; tried at Abingdon on October 31, when the guilty verdict came the same day the trial opened; released on November 4, 1887 when judgment was arrested and the conviction fell. She spent 190 days in custody — about six months and one week — and was never sentenced.";
$case->setPartialDate("arrest_date", 1887, 4, 28);
$case->setPartialDate("incarceration_date", 1887, 4, 28);
$case->setPartialDate("release_date", 1887, 11, 4);
$case->save();

$p->refresh();
echo "Elmina Drake Slenker  [{$p->slug}]  aka ".($p->aka ?: "-")."\n";
echo "  born ".($p->formatPartialDate("birthdate") ?: "-")
    ."   died ".($p->formatPartialDate("death_date") ?: "-")
    ."   age ".($p->age ?? "-")."  (expect 80)\n";
echo "  arrest/incarceration ".$case->incarceration_date->toDateString()
    ."   release ".$case->release_date->toDateString()."\n";
echo "  imprisoned_for_days = ".($case->imprisoned_for_days ?? "null")."  (expect 190)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
