#!/usr/bin/env bash
#
# Dr. Walter A. Matthey -- corrected name and custody dates.
#
# The record (slug dr-walter-c-matthey) carried the middle initial "C."
# from a later secondary source; contemporary medical reporting and
# newspaper accounts use Walter A. Matthey. The name is corrected and the
# C. variant kept as an alias so searches still find him. The slug is
# left alone -- the URL is published and the slug is only an address.
#
# The custody dates contained an impossibility: Leavenworth from
# October 18, 1918 through April 3, 1922 -- nearly three and a half years
# of a sentence of ONE YEAR AND ONE DAY (plus a $1,000 fine), imposed
# after his October 1918 conviction, with appeals still running through
# 1921 (Eighth Circuit affirmed August 10, 1921) and into 1922 (Supreme
# Court declined review in January 1922).
#
#   Jul 25, 1917       the Davenport, Iowa meeting at which Daniel H.
#                      Wallace spoke; Matthey helped organize it,
#                      distributed pamphlets previewing the speech,
#                      attended, applauded, and reportedly contributed
#                      25 cents
#   Oct 1918           convicted of aiding and abetting under the
#                      Espionage Act
#   Aug 10, 1921       Eighth Circuit affirms -- his participation "not
#                      merely accidental"
#   Jan 1922           Supreme Court declines review
#   c. Apr 3, 1921 --  best reconstructed Leavenworth custody: a federal
#      Apr 3, 1922     report gives April 4, 1922 as the scheduled
#                      expiration of the term, and one year and one day
#                      back from that is about April 3, 1921. The entry
#                      date is an inference, not yet confirmed from the
#                      Leavenworth admission register, and the case text
#                      says so.
#   Apr 6, 1922        pardoned by President Harding, the fine left
#                      intact; one source places release on April 3,
#                      possibly separating physical release from the
#                      formal pardon
#
#   April 3, 1921 to April 3, 1922 = 365 days. The counter drops from
#   1263.
#
# The famous clemency-summary description (attended, listened, applauded
# statements of supposedly unknown nature, gave 25 cents) is kept in the
# bio alongside what the Eighth Circuit actually cited -- organizing,
# pamphleting, applauding -- since the summary alone understates the
# record on which the conviction stood.
#
# ARREST DATE stays unset and LIFE DATES stay unset: no source in hand
# documents them, so none are invented.
#
# The derived "years in prison" list self-corrects from 1918-1922 to
# 1921-1922 on save.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-walter-matthey.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "dr-walter-c-matthey")->with("cases")->first();
if (! $p) {
    echo "NOT FOUND: dr-walter-c-matthey\n";
    exit(1);
}

$p->name = "Dr. Walter A. Matthey";
$p->first_name = "Walter";
$p->middle_name = "A.";
$p->last_name = "Matthey";
$p->aka = "Walter C. Matthey";
$p->gender = "Male";
$p->state = "Iowa";
$p->in_custody = false;
$p->released = true;
$p->awaiting_trial = false;
$p->ideologies = ["Anti-War", "Anti-Militarism"];
$p->description = "Dr. Walter A. Matthey, a Davenport, Iowa physician, helped arrange a July 25, 1917 meeting at which Daniel H. Wallace delivered an anti-war and anti-conscription speech. Matthey distributed pamphlets announcing the address, attended and applauded, and reportedly contributed 25 cents. An attorney-general clemency summary later described the case as no more than attending, listening, applauding statements whose exact nature was supposedly unknown, and contributing a quarter, but the Eighth Circuit cited more: he helped organize the meeting, handed out the pamphlets previewing the speech, and applauded, so that his participation was not merely accidental. Federal authorities convicted him in October 1918 of aiding and abetting an attempt to cause insubordination, disloyalty and refusal of military duty. He received one year and one day in Leavenworth Penitentiary and a \$1,000 fine. The Eighth Circuit affirmed the conviction on August 10, 1921, and the Supreme Court declined review in January 1922. His best reconstructed imprisonment dates are approximately April 3, 1921 to April 3, 1922 — the entry date is inferred from the sentence length and a federal report giving April 4, 1922 as the scheduled expiration of the term, and still requires confirmation from the Leavenworth admission register. A published chronology placing him at Leavenworth continuously from October 18, 1918 cannot be right for a sentence of a year and a day whose appeals ran into 1922. President Warren G. Harding formally pardoned him on or about April 6, 1922, without canceling the fine. One later secondary source gives his middle initial as C., but contemporary medical reporting and newspaper accounts use Walter A. Matthey.";
$p->save();

$leavenworth = Institution::firstOrCreate(
    ["name" => "USP Leavenworth"],
    ["city" => "Leavenworth", "state" => "Kansas"],
);

$case = $p->cases->first() ?? $p->cases()->create([]);
$case->institution_id = $leavenworth->id;
$case->charges = "Espionage Act of 1917 — aiding and abetting Daniel H. Wallace in attempting to cause insubordination, disloyalty and refusal of duty in the United States military, arising from the July 25, 1917 meeting in Davenport, Iowa that Matthey helped organize and at which Wallace spoke.";
$case->convicted = "Yes — October 1918. The Eighth Circuit affirmed on August 10, 1921, citing that Matthey helped organize the meeting, distributed pamphlets previewing the speech, attended and applauded — participation not merely accidental. The Supreme Court declined review in January 1922.";
$case->sentence = "One year and one day in the United States Penitentiary at Leavenworth and a \$1,000 fine. Custody is recorded from about April 3, 1921 to April 3, 1922: a federal report gives April 4, 1922 as the scheduled expiration of the term, and one year and one day back from that puts the entry at about April 3, 1921 — an inference not yet confirmed from the Leavenworth admission register. A published chronology putting him inside from October 18, 1918 through April 3, 1922 cannot represent continuous imprisonment of this sentence, since his appeals continued through 1921 and into 1922; this record corrects it. President Harding pardoned him on or about April 6, 1922, leaving the fine intact; one source places the release on April 3, which may distinguish physical release from the formal pardon date.";
$case->setPartialDate("incarceration_date", 1921, 4, 3);
$case->setPartialDate("release_date", 1922, 4, 3);
$case->save();

$p->refresh()->load("cases");
$case = $p->cases->first();
echo "{$p->name}  [{$p->slug}]  aka ".($p->aka ?: "-")."\n";
echo "  incarcerated ".$case->incarceration_date->toDateString()."   released ".$case->release_date->toDateString()."\n";
echo "  imprisoned_for_days = ".($case->imprisoned_for_days ?? "null")."  (expect 365, was 1263)\n";
echo "  years in prison: ".implode(", ", $p->years_in_prison ?: [])."  (expect 1921, 1922)\n";
echo "  born ".($p->formatPartialDate("birthdate") ?: "(unset)")."   died ".($p->formatPartialDate("death_date") ?: "(unset)")."\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
