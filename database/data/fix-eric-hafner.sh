#!/usr/bin/env bash
#
# Eric G. Hafner -- corrected sentencing year and full case timeline.
#
# The record said he was sentenced in December 2022, in both the bio and
# the case sentence text. He was sentenced on DECEMBER 7, 2023. The
# guilty plea came about eighteen months earlier, on May 17, 2022, and
# the 2022 date appears to have been carried forward onto the sentencing
# by mistake.
#
# The case row also had no dates on it at all -- no arrest, no
# incarceration, no indictment, no sentencing date -- so the profile
# showed a man serving twenty years with an imprisonment counter of
# zero. The full chronology is now recorded:
#
#   Oct  6, 2016   federal complaint filed under seal, one count of
#                  transmitting threatening communications, 18 U.S.C.
#                  section 875(c)
#   Sep 28, 2019   arrested at the airport on Saipan
#   Sep 30, 2019   initial appearance in the Northern Mariana Islands
#                  before Chief U.S. District Judge Ramona V. Manglona
#   Oct 23, 2019   produced in the District of New Jersey before
#                  Magistrate Judge Tonianne J. Bongiovanni and ordered
#                  detained without bail
#   Oct 31, 2019   indicted on 33 counts -- nine extortionate threats,
#                  eighteen interstate threats, six false bomb threats
#   May 17, 2022   pleaded guilty to three representative counts under
#                  18 U.S.C. sections 875(b), 875(c) and 844(e)
#   Nov 24, 2022   motion to withdraw the plea denied; renewed and
#                  refused again before sentencing
#   Dec  7, 2023   sentenced to 240 months -- twenty years -- and three
#                  years of supervised release
#   Feb 27, 2026   Third Circuit affirms both the refusal to let him
#                  withdraw the plea and the 240-month sentence
#
# THE ARREST DATE IS SEPTEMBER 28. A federal district-court opinion
# gives the 28th. Justice Department publicity and some news reports say
# the 27th, apparently reflecting the local date across the date line or
# the moment authorities first took him into custody. The case text
# records the conflict rather than hiding it.
#
# CUSTODY RUNS FROM THE ARREST. He was detained without bail from his
# first appearance in New Jersey and has never been released, so
# incarceration is dated to the arrest and the release date stays empty.
# Because the record is flagged in custody, the counter now runs to the
# present day instead of sitting at zero. The projected release of
# October 12, 2036 stays in the sentence text where it belongs -- it is
# a Bureau of Prisons projection, not a release that has happened.
#
# THE SENTENCING JUDGE IS NOT RECORDED. Judges Manglona and Bongiovanni
# handled the initial appearance and the detention order respectively
# and are named in the case narrative, but neither sentenced him and no
# source in hand names who did, so the judge field is left empty rather
# than filled with the wrong name.
#
# THE BIRTHDATE IS LEFT ALONE. The bio gives a birth year of 1991 with
# no month or day, and the record carries a stored age of 34. Setting a
# year-precision birthdate would default to January 1 and recompute the
# age to 35, which may well be wrong. Left for a source that gives the
# actual date.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-eric-hafner.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "eric-hafner")->with("cases")->first();
if (! $p) {
    echo "NOT FOUND: eric-hafner\n";
    exit(1);
}

$p->in_custody = true;
$p->released = false;
$p->awaiting_trial = false;
$p->description = "Eric G. Hafner (born 1991, New Jersey) is a perennial political candidate serving a twenty-year federal sentence at the Federal Correctional Institution in Otisville, New York, with a projected release in October 2036. A federal complaint charging him with transmitting threatening communications was filed under seal on October 6, 2016. After years living abroad and in hiding he was arrested on September 28, 2019 at the airport on Saipan in the Northern Mariana Islands, appeared two days later before Chief U.S. District Judge Ramona V. Manglona, and was produced in the District of New Jersey on October 23, 2019, where Magistrate Judge Tonianne J. Bongiovanni ordered him detained without bail. A federal grand jury indicted him on October 31, 2019 on thirty-three counts — nine of extortionate threats, eighteen of interstate threats and six of conveying false bomb threats — arising from messages threatening to kill or injure New Jersey elected officials, police officers, attorneys and judges and their families. He pleaded guilty on May 17, 2022 to three representative counts, then moved to withdraw the plea; the motion was denied on November 24, 2022 and refused again when he renewed it. He was sentenced on December 7, 2023 to 240 months in federal prison followed by three years of supervised release. The Third Circuit affirmed on February 27, 2026, upholding both the refusal to let him withdraw the plea and the length of the sentence. He has been in continuous federal custody since his arrest in 2019, and ran for office from prison.";
$p->save();

$case = $p->cases->first() ?? $p->cases()->create([]);
$case->charges = "Thirty-three counts in the October 31, 2019 federal indictment — nine counts of extortionate threats, eighteen counts of transmitting threats in interstate commerce and six counts of conveying false bomb threats — over messages threatening to kill or injure New Jersey elected officials, police officers, attorneys and judges and their families. The case began with a complaint filed under seal on October 6, 2016 charging a single count of transmitting threatening communications under 18 U.S.C. section 875(c).";
$case->indicted = "October 31, 2019 — thirty-three counts.";
$case->convicted = "Pleaded guilty on May 17, 2022 to three representative counts under 18 U.S.C. sections 875(b), 875(c) and 844(e). He moved to withdraw the plea; the motion was denied on November 24, 2022 and denied again when renewed before sentencing. The Third Circuit affirmed the refusal on February 27, 2026.";
$case->sentence = "240 months — twenty years — in federal prison plus three years of supervised release, imposed December 7, 2023, NOT in December 2022: the earlier date belongs to the guilty plea year and had been carried onto the sentencing by mistake. Projected release from the Bureau of Prisons is October 12, 2036. He was arrested on Saipan and held without bail from his first New Jersey appearance on October 23, 2019, and has been in continuous custody since, which is why the sentence computation credits time from 2019. Some Justice Department publicity and news reports give the arrest as September 27; a federal district-court opinion gives September 28, which is the date used here, the discrepancy apparently reflecting the local date across the date line or the moment authorities first took him into custody. His initial appearance in the Northern Mariana Islands on September 30, 2019 was before Chief U.S. District Judge Ramona V. Manglona, and the detention order was entered by Magistrate Judge Tonianne J. Bongiovanni; neither sentenced him, and no source in hand names the sentencing judge, so that field is left empty.";
$case->setPartialDate("arrest_date", 2019, 9, 28);
$case->setPartialDate("incarceration_date", 2019, 9, 28);
$case->setPartialDate("sentenced_date", 2023, 12, 7);
$case->release_date = null;
$case->save();

$p->refresh()->load("cases");
$case = $p->cases->first();
echo "Eric Hafner  [{$p->slug}]\n";
echo "  arrest       ".$case->arrest_date->toDateString()."\n";
echo "  incarcerated ".$case->incarceration_date->toDateString()."\n";
echo "  sentenced    ".$case->sentenced_date->toDateString()."   (expect 2023-12-07)\n";
echo "  released     ".($case->release_date ? $case->release_date->toDateString() : "(still in custody)")."\n";
echo "  imprisoned_for_days = ".($case->imprisoned_for_days ?? "null")."  (counts to today, was 0)\n";
echo "  years in prison: ".implode(", ", $p->years_in_prison ?: [])."\n";
echo "  bio mentions 2022 sentencing: ".(str_contains($p->description, "sentenced that December") ? "YES -- still wrong" : "no")."\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
