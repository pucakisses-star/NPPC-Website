#!/usr/bin/env bash
#
# Jacob Frohwerk -- corrected custody dates and life dates.
#
# The record said "Incarcerated: 1918", which conflates sentencing with
# imprisonment. Frohwerk was arrested and sentenced in 1918 but remained
# free on a \$7,500 appeal bond through his Supreme Court appeal, and did
# not enter Leavenworth until May 31, 1919. The record also carried a
# scrambled death date (1939-09-19 for November 19, 1949) and a stored age
# of 75; he died at about 84.
#
#   Jan 26, 1918   arrested with publisher Carl Gleeser
#   Apr 23, 1918   federal indictment
#   Jun 28, 1918   convicted (reconstructed from the June 29 report --
#                  noted as such on the case)
#   Jun 29, 1918   sentenced: ten years, \$500 fine and costs; free on
#                  \$7,500 appeal bond
#   Jan 27, 1919   argued at the Supreme Court
#   Mar 10, 1919   affirmed -- Frohwerk v. United States, 249 U.S. 204,
#                  Holmes writing
#   May 31, 1919   entered USP Leavenworth, prisoner 14036, cell B-175,
#                  having arranged an extra day to visit his daughter's
#                  grave
#   Jun 19, 1919   Wilson commuted the term to one year and one day
#   Sep 29, 1919   parole-eligible
#   Jan  6, 1920   parole approved
#   Jan 10, 1920   released -- paperwork pushed him a day past the
#                  expected January 9; a January 11 newspaper report
#                  confirms his departure
#
#   May 31, 1919 to January 10, 1920 = 224 days = exactly the thirty-two
#   weeks the archival study describes. The counter, previously zero,
#   now shows it.
#
# The derived "years in prison" list, which had ballooned to 1918-1939 off
# the bad dates, self-corrects to 1919-1920 on save.
#
# LIFE DATES. Death set to November 19, 1949 (his Kansas City, Kansas
# home). Birth stays UNSET: about 1864-65 in Germany, and a two-year
# window does not fit a year-precision field (the Barbara Katt rule) --
# the bio carries it instead. The stored age of 75 is cleared rather than
# replaced, since with no birthdate any displayed age would be a guess.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-jacob-frohwerk.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "jacob-frohwerk")->with("cases")->first();
if (! $p) {
    echo "NOT FOUND: jacob-frohwerk\n";
    exit(1);
}

$p->state = "Kansas";
$p->inmate_number = "14036";
$p->in_custody = false;
$p->released = true;
$p->awaiting_trial = false;
$p->age = null;
$p->setPartialDate("birthdate", null);
$p->setPartialDate("death_date", 1949, 11, 19);
$p->ideologies = ["Anti-War", "Press Freedom"];
$p->description = "Jacob Frohwerk was a Kansas City newspaperman prosecuted under the Espionage Act of 1917 for a series of articles in the German-language Missouri Staats-Zeitung criticizing American involvement in the First World War. He was arrested with the paper’s publisher, Carl Gleeser, on January 26, 1918, indicted on April 23, convicted on June 28 and sentenced the next day to ten years and a \$500 fine. He remained free on a \$7,500 appeal bond while his case went to the Supreme Court, which affirmed the conviction unanimously on March 10, 1919 in Frohwerk v. United States, 249 U.S. 204 — Justice Oliver Wendell Holmes writing, one of the trio of Espionage Act decisions handed down with Schenck and Debs that spring — rejecting his First Amendment defense. He entered the federal penitentiary at Leavenworth on May 31, 1919 as prisoner 14036, assigned to cell B-175, having arranged an extra day of liberty to visit his daughter’s grave before surrendering. Nineteen days later, on June 19, 1919, President Woodrow Wilson commuted the ten-year term to one year and one day. Parole-eligible from September 29, he was approved by the parole board on January 6, 1920 and released on January 10 — paperwork having pushed him a day past the expected January 9 — after exactly thirty-two weeks inside. Born about 1864–65 in Germany (no reliable exact birth date has been located), he died at his Kansas City, Kansas home on November 19, 1949, aged about 84.";
$p->save();

$leavenworth = Institution::firstOrCreate(
    ["name" => "USP Leavenworth"],
    ["city" => "Leavenworth", "state" => "Kansas"],
);

$case = $p->cases->first() ?? $p->cases()->create([]);
$case->institution_id = $leavenworth->id;
$case->charges = "Espionage Act of 1917 — thirteen counts over articles in the German-language Missouri Staats-Zeitung criticizing American involvement in the First World War and the draft. Frohwerk v. United States, 249 U.S. 204 (1919).";
$case->indicted = "April 23, 1918";
$case->convicted = "Yes — June 28, 1918 (the day is reconstructed from the contemporary June 29 report). Affirmed unanimously by the Supreme Court on March 10, 1919, Holmes writing.";
$case->sentence = "Ten years, a \$500 fine and court costs, imposed June 29, 1918. He stayed free on a \$7,500 appeal bond until the Supreme Court affirmed, entering Leavenworth on May 31, 1919 as prisoner 14036, cell B-175. President Wilson commuted the term to one year and one day on June 19, 1919; parole was approved January 6, 1920 and he walked out on January 10, 1920 — thirty-two weeks, and the sentencing date of June 29, 1918 that some secondary accounts give as the start of imprisonment is the confusion this record corrects.";
$case->setPartialDate("arrest_date", 1918, 1, 26);
$case->setPartialDate("sentenced_date", 1918, 6, 29);
$case->setPartialDate("incarceration_date", 1919, 5, 31);
$case->setPartialDate("release_date", 1920, 1, 10);
$case->save();

$p->refresh()->load("cases");
$case = $p->cases->first();
echo "Jacob Frohwerk  [{$p->slug}]  inmate ".($p->inmate_number ?: "-")."\n";
echo "  arrest       ".$case->arrest_date->toDateString()."   sentenced ".$case->sentenced_date->toDateString()."\n";
echo "  incarcerated ".$case->incarceration_date->toDateString()."   released  ".$case->release_date->toDateString()."\n";
echo "  imprisoned_for_days = ".($case->imprisoned_for_days ?? "null")."  (expect 224 = 32 weeks)\n";
echo "  died ".($p->formatPartialDate("death_date") ?: "-")."   age shown: ".($p->age ?? "(none — birthdate unknown)")."\n";
echo "  years in prison: ".implode(", ", $p->years_in_prison ?: [])."  (expect 1919, 1920)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
