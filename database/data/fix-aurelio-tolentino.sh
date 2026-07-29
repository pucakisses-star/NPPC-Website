#!/usr/bin/env bash
#
# Aurelio Tolentino -- portrait, life dates, and three separate periods
# of custody.
#
# The record held one case whose arrest date was May 14, 1903 and whose
# release date was February 5, 1907, with NO incarceration date, so the
# counter read zero. Reading those two dates as one span would have been
# worse than zero: he was out on bail for roughly half of it.
#
# LIFE DATES: born October 15, 1869 in Guagua, Pampanga; died July 5,
# 1915 in Manila, aged 45. Full day precision.
#
# THREE PERIODS OF CUSTODY, each its own case row so the profile totals
# correctly:
#
#   1896            Arrested shortly after the Philippine Revolution
#                   began and held NINE MONTHS. The source gives no
#                   dates at all, only the length, so this row carries
#                   no dates and adds nothing to the counter. Nine
#                   months of real custody therefore sit outside the
#                   total, which is the honest result of having no
#                   dates rather than a gap to be filled by arithmetic.
#
#   May 14, 1903    Arrested the same day as the premiere of his three
#   Dec 1903        act drama Kahapon, Ngayon at Bukas -- Yesterday,
#                   Today and Tomorrow -- at the Teatro Libertad in
#                   Manila. Processed at the office of the Manila chief
#                   of police, taken to Bilibid, and sentenced to two
#                   years hard labour and a fine. Bailed in December
#                   1903 for 7,000 pesos. THE RELEASE IS MONTH
#                   PRECISION: the month is documented, the day is not.
#                   = 201 days, counting from May 14 to the first of
#                     December
#
#   Jun 14, 1904    Recaptured, in his own account, during negotiations.
#   Feb  5, 1907    Sentenced to six years and a fine of more than
#                   \$5,000. Paroled on February 5, 1907 on condition
#                   that he report his activities to the American
#                   authorities for five years.
#                   = 966 days
#
#   Countable total: 1,167 days, about three years and two months,
#   excluding the undated nine months of 1896.
#
# THE FINES ARE NOT CUSTODY and add nothing, consistent with the rest of
# the database.
#
# A SOURCE CONFLICT IS RECORDED, NOT RESOLVED SILENTLY. Tolentinos own
# 1908 autobiography gives the two-year sentence and the December 1903
# bail used here. Other scholarly accounts instead say he received a
# LIFE SENTENCE that Governor-General William Cameron Forbes later
# reduced. The autobiography is followed because it is first-hand and
# supplies the dates, and the disagreement is written onto the case so
# that a later reader can weigh it.
#
# THE PORTRAIT was supplied as a social-media graphic with a caption bar
# reading "Aurelio Tolentino: Manunulat at Rebolusyonaryo" across the
# bottom and a watermark in the corner; it is cropped to the photograph
# alone, 415x458, with both removed.
#
# Cases are keyed by markers in the charges text. The pre-existing
# unmarked row is adopted as the 1903 case. Run from the repo root:
#   bash database/data/fix-aurelio-tolentino.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$p = Prisoner::withoutGlobalScopes()->where("slug", "aurelio-tolentino")->with("cases")->first();
if (! $p) {
    echo "NOT FOUND: aurelio-tolentino\n";
    exit(1);
}

$p->first_name = "Aurelio";
$p->last_name = "Tolentino";
$p->gender = "Male";
$p->race = "Asian";
$p->state = "Philippines";
$p->in_custody = false;
$p->released = true;
$p->awaiting_trial = false;
$p->setPartialDate("birthdate", 1869, 10, 15);
$p->setPartialDate("death_date", 1915, 7, 5);
$p->ideologies = ["Philippine Independence", "Anti-Imperialism", "Press Freedom"];
$p->affiliation = ["Katipunan", "Junta de Amigos"];
$p->description = "Aurelio Tolentino was a Filipino playwright, poet, journalist and revolutionary who was imprisoned repeatedly under both Spanish and American rule. Born in Guagua, Pampanga on October 15, 1869, he was an early member of the Katipunan, and was arrested shortly after the Philippine Revolution began in 1896 and held for nine months. Under the American occupation he wrote the three-act drama Kahapon, Ngayon at Bukas — Yesterday, Today and Tomorrow — an allegory of the Philippines under Spanish and then American rule. At its premiere at the Teatro Libertad in Manila on May 14, 1903 the audience tore down an American flag on stage, and Tolentino was arrested the same day, processed at the office of the Manila chief of police and taken to Bilibid prison. By his own account he was sentenced to two years of hard labour and a fine, and was bailed in December 1903 for 7,000 pesos; other accounts hold that he was given a life sentence later reduced by Governor-General William Cameron Forbes. He was captured again on June 14, 1904, sentenced to six years and a fine of more than five thousand dollars, and paroled on February 5, 1907 on condition that he report his activities to the American authorities for the next five years. He later co-founded the Junta de Amigos, a secret society of former Katipuneros, and went on writing until his death in Manila on July 5, 1915, at forty-five.";
$p->save();

$src = database_path("data/photos/nonfree/aurelio-tolentino.jpg");
if (is_file($src)) {
    File::ensureDirectoryExists(storage_path("app/public/prisoners"));
    $dest = "prisoners/aurelio-tolentino.jpg";
    File::copy($src, storage_path("app/public/".$dest), true);
    touch(storage_path("app/public/".$dest));
    $p->photo = $dest;
    $p->save();
} else {
    echo "  photo file missing: {$src}\n";
}

$bilibid = Institution::firstOrCreate(
    ["name" => "Old Bilibid Prison"],
    ["city" => "Manila", "state" => "Philippines"],
);

// marker, charges, sentence, arrest, incarceration, release, institution
$rows = [
    [
        "[revolution-1896]",
        "Arrested shortly after the outbreak of the Philippine Revolution against Spain, as an early member of the Katipunan.",
        "Detained nine months. THE SOURCE GIVES NO DATES, only the length, so none are recorded and this term adds nothing to the imprisonment counter — nine months of real custody sitting outside the total is the honest consequence of having no dates, not a figure to be reconstructed by arithmetic.",
        null, null, null, false,
    ],
    [
        "[kahapon-1903]",
        "Sedition — for staging the three-act drama Kahapon, Ngayon at Bukas (Yesterday, Today and Tomorrow) at the Teatro Libertad in Manila on May 14, 1903, an allegory of Philippine subjection under Spain and then the United States. He was arrested the same day as the premiere.",
        "Two years of hard labour and a fine, by his own 1908 account. He was processed at the office of the Manila chief of police, taken to Bilibid, and bailed in December 1903 at a cost of 7,000 pesos. THE RELEASE IS RECORDED TO THE MONTH ONLY: December 1903 is documented, the day is not, so the span is counted from the first of the month. A SOURCE CONFLICT IS OPEN HERE — other scholarly accounts say the sentence was life imprisonment, later reduced by Governor-General William Cameron Forbes. His own autobiography is followed because it is first-hand and supplies the dates, but the disagreement is noted rather than settled. The fine is not custody.",
        [1903, 5, 14], [1903, 5, 14], [1903, 12], true,
    ],
    [
        "[recapture-1904]",
        "Recaptured on June 14, 1904 — by his account, during negotiations — and returned to prison on the sedition case.",
        "Six years and a fine of more than five thousand dollars. He was paroled on February 5, 1907, after 966 days, on condition that he report his activities to the American authorities for five years. The fine is not custody and adds nothing to the total.",
        [1904, 6, 14], [1904, 6, 14], [1907, 2, 5], true,
    ],
];

foreach ($rows as [$marker, $charges, $sentence, $arrest, $incarceration, $release, $useInst]) {
    $case = $p->cases->first(fn ($c) => str_contains((string) $c->charges, $marker));
    if (! $case && $marker === "[kahapon-1903]") {
        $case = $p->cases->first(fn ($c) => ! preg_match("/\[[a-z0-9-]+\]/", (string) $c->charges));
    }
    $case = $case ?? $p->cases()->make([]);
    $case->prisoner_id = $p->id;
    $case->charges = $marker." ".$charges;
    $case->sentence = $sentence;
    $case->institution_id = $useInst ? $bilibid->id : null;
    foreach ([["arrest_date", $arrest], ["incarceration_date", $incarceration], ["release_date", $release]] as [$field, $val]) {
        if ($val) {
            $case->setPartialDate($field, ...$val);
        } else {
            $case->{$field} = null;
        }
    }
    $case->save();
}

$p->refresh()->load("cases");
echo "\n{$p->name}  [{$p->slug}]\n";
echo "  born ".($p->formatPartialDate("birthdate") ?: "-")."   died ".($p->formatPartialDate("death_date") ?: "-")."   age ".($p->age ?? "-")."  (expect Oct 15 1869, Jul 5 1915, 45)\n";
echo "  photo ".($p->photo ?: "(none)")."\n";
$total = 0;
foreach ($p->cases->sortBy("incarceration_date") as $c) {
    preg_match("/\[([a-z0-9-]+)\]/", (string) $c->charges, $m);
    $total += (int) $c->imprisoned_for_days;
    echo "  ".str_pad($m[1] ?? "?", 18)
        ." inc ".str_pad($c->formatPartialDate("incarceration_date") ?: "-", 14)
        ." rel ".str_pad($c->formatPartialDate("release_date") ?: "-", 14)
        ." days ".($c->imprisoned_for_days ?? "null")."\n";
}
echo "  TOTAL {$total} days  (expect 1167 = 201 + 966; the 1896 nine months are undated and excluded)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
