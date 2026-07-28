#!/usr/bin/env bash
#
# Carl Henry Gleeser -- corrected record.
#
# The record was a stub: no dates at all, "Sentence not stated," sourced
# only to the 1921 Senate amnesty hearing. He was Jacob Frohwerk's
# publisher and co-defendant, and unlike Frohwerk -- whose record was just
# corrected on the same facts -- Gleeser pleaded guilty, went straight to
# Leavenworth in 1918, and never had a Supreme Court appeal.
#
#   Aug 1855       born in Germany -- exact day unknown, so the field gets
#                  MONTH precision, not an invented day
#   Jan 26, 1918   arrested with Frohwerk
#   Apr 23, 1918   indicted on thirteen counts
#   Apr 30, 1918   pleaded guilty, sentenced to five years, and entered
#                  USP Leavenworth the same day -- prisoner 12644. He
#                  agreed to testify for the government against Frohwerk,
#                  and was already inside when Frohwerk went to trial
#   May  8, 1919   Wilson commutation (to one year and one day) reached
#                  the warden and he was released immediately -- by then
#                  he had already served slightly longer than the
#                  commuted term
#   Mar 30, 1947   died at his New Llano, Louisiana residence
#
#   April 30, 1918 to May 8, 1919 = 373 days -- one year and eight days.
#
# The Supreme Court case that discusses the conspiracy, Frohwerk v.
# United States, was FROHWERK's appeal, not his; the case text says so to
# keep the two records straight.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-carl-gleeser.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "carl-gleeser")->with("cases")->first();
if (! $p) {
    echo "NOT FOUND: carl-gleeser\n";
    exit(1);
}

$p->middle_name = "Henry";
$p->gender = "Male";
$p->state = "Missouri";
$p->inmate_number = "12644";
$p->in_custody = false;
$p->released = true;
$p->awaiting_trial = false;
$p->setPartialDate("birthdate", 1855, 8);
$p->setPartialDate("death_date", 1947, 3, 30);
$p->ideologies = ["Anti-War", "Press Freedom"];
$p->description = "Carl Henry Gleeser owned, published, edited, printed and distributed the German-language Kansas City newspaper Missouri Staats-Zeitung, which carried Jacob Frohwerk’s series of editorials opposing American participation in the First World War. The articles argued, among other things, that the United States had entered the war for British and financial interests, that Germany was fighting defensively, and that working-class Americans were being conscripted to protect wealthy investors; the government charged that twelve articles published during 1917 were intended to cause insubordination and disloyalty in the armed forces and to obstruct recruitment and conscription. Gleeser and Frohwerk were arrested together on January 26, 1918 and indicted on thirteen counts on April 23. Gleeser chose a different course from his co-defendant: he pleaded guilty on April 30, 1918, agreed to testify for the government against Frohwerk, drew five years — half of Frohwerk’s eventual ten — and entered the federal penitentiary at Leavenworth the same day as prisoner 12644, so that he was already inside when Frohwerk went to trial that summer. The Supreme Court case that discusses their alleged conspiracy, Frohwerk v. United States, 249 U.S. 204 (1919), was Frohwerk’s appeal; Gleeser never appealed. President Woodrow Wilson commuted Gleeser’s sentence to one year and one day, and when the Justice Department notice reached the Leavenworth warden on May 8, 1919 he was released immediately — after 373 days, slightly longer than the commuted term. He later joined the cooperative colony at New Llano, Louisiana, where he edited the Llano Colonist and helped administer the colony. Born in Germany in August 1855, he died at his New Llano residence late on March 30, 1947 and is buried in the colony’s Gill Cemetery.";
$p->save();

$leavenworth = Institution::firstOrCreate(
    ["name" => "USP Leavenworth"],
    ["city" => "Leavenworth", "state" => "Kansas"],
);

$case = $p->cases->first() ?? $p->cases()->create([]);
$case->institution_id = $leavenworth->id;
$case->charges = "Espionage Act of 1917 — thirteen counts, as publisher of the Missouri Staats-Zeitung, over twelve 1917 articles alleged to be intended to cause insubordination and disloyalty in the armed forces and to obstruct recruitment and conscription. The first count charged a conspiracy with Jacob Frohwerk to prepare and circulate the articles.";
$case->indicted = "April 23, 1918";
$case->plead = "Guilty — pleaded April 30, 1918 and agreed to testify for the government against Frohwerk";
$case->convicted = "Yes, on his guilty plea. He never appealed; Frohwerk v. United States, 249 U.S. 204 (1919), which discusses the alleged conspiracy, was his co-defendant Frohwerk’s appeal, not his.";
$case->sentence = "Five years, imposed April 30, 1918 on his guilty plea — half of Frohwerk’s eventual ten — and he entered Leavenworth the same day as prisoner 12644. President Wilson commuted the term to one year and one day; the Justice Department notice reached the warden on May 8, 1919 and Gleeser was released immediately, having already served 373 days, slightly longer than the commuted sentence.";
$case->setPartialDate("arrest_date", 1918, 1, 26);
$case->setPartialDate("sentenced_date", 1918, 4, 30);
$case->setPartialDate("incarceration_date", 1918, 4, 30);
$case->setPartialDate("release_date", 1919, 5, 8);
$case->save();

$p->refresh()->load("cases");
$case = $p->cases->first();
echo "Carl Henry Gleeser  [{$p->slug}]  inmate ".($p->inmate_number ?: "-")."\n";
echo "  born ".($p->formatPartialDate("birthdate") ?: "-")."   died ".($p->formatPartialDate("death_date") ?: "-")."   age ".($p->age ?? "-")."  (expect 91)\n";
echo "  arrest       ".$case->arrest_date->toDateString()."   plead/sentenced ".$case->sentenced_date->toDateString()."\n";
echo "  incarcerated ".$case->incarceration_date->toDateString()."   released ".$case->release_date->toDateString()."\n";
echo "  imprisoned_for_days = ".($case->imprisoned_for_days ?? "null")."  (expect 373)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
