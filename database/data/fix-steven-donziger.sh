#!/usr/bin/env bash
#
# Steven Donziger -- corrected and completed record.
#
# The existing record was thin and partly wrong: the bio misspelled Ecuador
# as "Equator" and described the contamination as an oil spill (it was
# deliberate discharge of production waste), and the single case carried
# nothing but a release date of 2022-04-24 -- off by a day, and semantically
# the sentence-completion date rather than his release from prison.
#
# Corrected chronology:
#
#   Aug  6, 2019  charged with six misdemeanor counts of criminal contempt;
#                 placed under GPS-monitored home confinement awaiting trial
#   Jul 26, 2021  found guilty at a bench trial (Judge Loretta A. Preska;
#                 court-appointed special prosecutor Rita M. Glavin, after
#                 the U.S. Attorney declined to prosecute)
#   Oct  1, 2021  sentenced to six months imprisonment
#   Oct 27, 2021  entered FCI Danbury
#   Dec  9, 2021  transferred to home confinement after about six weeks
#   Apr 25, 2022  completed the sentence
#
# THE COUNTER counts physical imprisonment only: October 27 to December 9,
# 2021 = 43 days, the same standard applied to Heather Doyle. The two
# home-confinement spans and the 993 continuous days of court-ordered
# restraint are recorded in the sentence text and the bio, where the reader
# gets them with their meaning attached, not silently inflated into the
# "time imprisoned" figure.
#
# The case is linked to the existing FCI Danbury institution (shared with
# Ted Glick). His middle name Robert is added; birthdate (September 14,
# 1961), ideologies, era and inmate number 87103-054 were already right and
# are untouched.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-steven-donziger.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "steven-donziger")->with("cases")->first();
if (! $p) {
    echo "NOT FOUND: steven-donziger\n";
    exit(1);
}

$p->middle_name = "Robert";
$p->in_custody = false;
$p->released = true;
$p->awaiting_trial = false;
$p->description = "Steven Robert Donziger, born September 14, 1961, is an American environmental and human-rights lawyer who represented Indigenous peoples and rural communities affected by oil contamination in Ecuador’s Amazon region. Beginning in 1993 he participated in litigation alleging that Texaco — acquired by Chevron in 2001 — had discharged billions of gallons of oil-production waste into local lands and waterways, and in 2011 an Ecuadorian court found Chevron liable, imposing a cleanup and damages judgment eventually reduced to approximately \$9.5 billion. Chevron answered with civil racketeering litigation in federal court in New York, which found the Ecuadorian judgment had been procured through fraud and entered injunctions and discovery orders against Donziger; he disputed those findings and resisted several orders, arguing in part that surrendering his electronic devices would expose privileged information belonging to his Ecuadorian clients. Charged with six misdemeanor counts of criminal contempt, he was placed under GPS-monitored home confinement on August 6, 2019, and after the U.S. Attorney declined to prosecute, the court appointed private special prosecutors. He was found guilty at a bench trial on July 26, 2021 and sentenced on October 1 to six months’ imprisonment. He entered FCI Danbury on October 27, 2021, was transferred to home confinement on December 9 after approximately six weeks in prison, and completed his sentence on April 25, 2022 — 993 continuous days under court-ordered restraint in all, counting the pretrial restrictions, the imprisonment and the sentence-related home confinement. Amnesty International and the United Nations Working Group on Arbitrary Detention criticized the proceedings and called his detention arbitrary.";
$p->save();

$danbury = Institution::firstOrCreate(
    ["name" => "FCI Danbury"],
    ["city" => "Danbury", "state" => "Connecticut"],
);

$case = $p->cases->first() ?? $p->cases()->create([]);
$case->institution_id = $danbury->id;
$case->charges = "Six misdemeanor counts of criminal contempt of court, for refusing to comply with discovery and related orders in Chevron’s civil racketeering case — among them an order to surrender his electronic devices, which he argued would expose privileged information belonging to his Ecuadorian clients.";
$case->plead = "Not guilty";
$case->convicted = "Yes — found guilty at a bench trial on July 26, 2021";
$case->prosecutor = "Rita M. Glavin, court-appointed special prosecutor (the U.S. Attorney declined to prosecute)";
$case->judge = "Loretta A. Preska";
$case->sentence = "Six months’ imprisonment, imposed October 1, 2021. He served approximately six weeks at FCI Danbury (October 27 – December 9, 2021), was transferred to home confinement for the remainder, and completed the sentence on April 25, 2022. Including the GPS-monitored pretrial home confinement that began August 6, 2019, he spent 993 continuous days under court-ordered restraint. The counter above counts only the physical imprisonment at Danbury.";
$case->setPartialDate("arrest_date", 2019, 8, 6);
$case->setPartialDate("sentenced_date", 2021, 10, 1);
$case->setPartialDate("incarceration_date", 2021, 10, 27);
$case->setPartialDate("release_date", 2021, 12, 9);
$case->save();

echo "Steven Robert Donziger  [{$p->slug}]\n";
echo "  arrest       ".$case->arrest_date->toDateString()."  (charged; GPS home confinement began)\n";
echo "  sentenced    ".$case->sentenced_date->toDateString()."\n";
echo "  incarcerated ".$case->incarceration_date->toDateString()."  (FCI Danbury)\n";
echo "  released     ".$case->release_date->toDateString()."  (to home confinement; sentence ran to 2022-04-25)\n";
echo "  imprisoned_for_days = ".($case->imprisoned_for_days ?? "null")."  (expect 43)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
