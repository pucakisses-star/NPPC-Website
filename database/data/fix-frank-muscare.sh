#!/usr/bin/env bash
#
# FRANK J. MUSCARE -- his case row had no dates at all, so a man who was
# jailed showed nothing served.
#
# CUSTODY. Incarcerated 1980-02-21, released 1980-03-14, Cook County
# Jail. The first date is documented: the Illinois Appellate Court{39}s
# recital of City of Chicago v. Chicago Fire Fighters Union, Local No. 2,
# 99 Ill. App. 3d 583 (1981), shows that on February 21, 1980 the trial
# court let the city withdraw from the collapsed back-to-work agreement,
# reinstated the coercive fines, found Muscare guilty of DIRECT CRIMINAL
# CONTEMPT and sentenced him to five months; wire coverage adds that he
# was taken to Cook County Jail straight from the hearing.
#
# The release date is corroborated from the other end rather than stated:
# UPI reports he "served 23 days of the sentence before his release on a
# \$100,000 appeal bond after the strike ended", and February 21 through
# March 14 inclusive is exactly 23 days. (The day counter on this record
# will read 22, because it measures the interval rather than counting
# both end days -- the same figure the appellate court used when it
# called the walkout "this 22-day strike".)
#
# WHY 23 DAYS OF A FIVE-MONTH SENTENCE. Not a purge, not time served,
# not a commutation -- the trial court expressly REFUSED to reconsider or
# commute the criminal contempt on March 13, 1980, converting only the
# daily fines to fixed sums (\$5,000 for him, \$40,000 for the union). He
# got out on a \$100,000 appeal bond once the strike was settled. The
# Appellate Court affirmed both the five-month sentence and the union
# fine on August 12, 1981, holding the same conduct may carry both
# coercive civil and punitive criminal contempt, and on March 22, 1982
# the Illinois Supreme Court declined review, leaving him facing a
# return to jail to finish the term. WHETHER HE WENT BACK IS UNRESOLVED:
# UPI reported only that he "will be ordered back" absent further legal
# steps or leniency, and no source settles it. The sentence text says so
# rather than implying the 23 days were the end of it.
#
# LIFE DATES. Birth 1925-11-08, death 2005-02-28, from a Find a Grave
# memorial and a Chicago Sun-Times obituary listing. Both pages refuse
# direct fetching, so the dates come from their search extracts -- but
# they agree with each other and with an independent check: UPI called
# him 56 on March 22, 1982, which puts his birth between March 1925 and
# March 1926, and November 8, 1925 falls inside that. The existing bio
# already said he died in 2005. Recorded at day precision on that basis;
# worth re-reading the obituary itself if anyone gets access.
#
# NAME. The middle initial is confirmed by the opinion itself, which
# names "Chicago Fire Fighters Union, Local No. 2, and its president,
# Frank J. Muscare." He was known in the department as "Moon".
#
# JUDGE recorded as John Hechinger, from UPI and Firehouse coverage; the
# opinion says only "the trial court", and his middle initial is not
# confirmed, so it is left off. No prosecutor: the city brought the
# contempt through Corporation Counsel, so that field stays empty.
#
# NOT RECORDED, deliberately: it is plausible but unconfirmed that he is
# the Francis Muscare of Muscare v. Quinn, 520 F.2d 1212 (7th Cir.
# 1975) -- a Chicago firefighter suspended in 1974 over a goatee who won
# a due-process ruling. Same surname, same department, same era, no
# source linking them. It is not written here.
#
# Every write is guarded: dates and life dates are only filled if empty,
# and the appended sentence note fires only once.
#
# SLUG SAFETY, checked rather than assumed. Prisoner has an updating hook
# that regenerates the slug, and setting a middle name looks exactly like
# the thing that broke the Matthey and Meyers scripts (they went NOT
# FOUND on re-run because a rename had moved the slug underneath them).
# Here it is safe: the hook fires only when NAME is dirty, and this
# script never touches name. The slug stays frank-muscare, the public URL
# keeps working, and a second run still finds him.
#
# Run from the repo root:
#   bash database/data/fix-frank-muscare.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "frank-muscare")->with("cases")->first();
if (! $p) {
    echo "NOT FOUND: frank-muscare\n";
    exit(1);
}

$changed = [];

if (empty($p->middle_name)) {
    $p->middle_name = "J.";
    $changed[] = "middle_name J.";
}
if (empty($p->aka)) {
    $p->aka = "Moon";
    $changed[] = "aka Moon";
}
if (! $p->birthdate) {
    $p->setPartialDate("birthdate", 1925, 11, 8);
    $changed[] = "birthdate 1925-11-08";
}
if (! $p->death_date) {
    $p->setPartialDate("death_date", 2005, 2, 28);
    $changed[] = "death 2005-02-28";
}
if ($changed) {
    $p->save();
}
echo "Prisoner: ", ($changed ? implode(", ", $changed) : "nothing to change"), "\n";
echo "  born ", (string) ($p->formatPartialDate("birthdate") ?? "—"),
     "   died ", (string) ($p->formatPartialDate("death_date") ?? "—"),
     "   age ", var_export($p->age, true), "\n";

$case = $p->cases->first();
if (! $case) {
    echo "NO CASE ROW — expected one; nothing else to do.\n";
    exit(1);
}

$caseChanged = [];

if (! $case->incarceration_date) {
    $case->incarceration_date = "1980-02-21";
    $caseChanged[] = "incarcerated 1980-02-21";
}
if (! $case->release_date) {
    $case->release_date = "1980-03-14";
    $caseChanged[] = "released 1980-03-14";
}
if (! $case->judge) {
    $case->judge = "John Hechinger";
    $caseChanged[] = "judge";
}
if (! $case->institution_id) {
    $inst = Institution::firstOrCreate(
        ["name" => "Cook County Jail"],
        ["city" => "Chicago", "state" => "Illinois"]
    );
    $case->institution_id = $inst->id;
    $caseChanged[] = "institution Cook County Jail".($inst->wasRecentlyCreated ? " (new)" : " (existing)");
}

$note = "He served 23 days of the five-month term before being released on a \$100,000 appeal bond once the strike was settled. This was not a purge of the contempt and not time served: on March 13, 1980 the trial court refused to reconsider or commute the criminal contempt, converting only the daily fines to fixed sums of \$5,000 for Muscare and \$40,000 for the union. The Illinois Appellate Court affirmed both the sentence and the fine on August 12, 1981, and on March 22, 1982 the Illinois Supreme Court declined review, leaving him facing a return to Cook County Jail to finish the term; whether he did is not established by any available source.";

if (! str_contains((string) $case->sentence, "appeal bond")) {
    $case->sentence = trim(($case->sentence ? rtrim($case->sentence)."\n\n" : "").$note);
    $caseChanged[] = "sentence note appended";
}

if ($caseChanged) {
    $case->save();
}

echo "Case: ", ($caseChanged ? implode(", ", $caseChanged) : "nothing to change"), "\n";
echo "  imprisoned_for_days = ", var_export($case->imprisoned_for_days, true), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
