#!/usr/bin/env bash
#
# Junius Irving Scales -- corrected incarceration dates.
#
# Every custody date on the record was wrong, and the counter was wrong by
# nearly a thousand days as a result:
#
#   field                was            should be
#   arrest_date          1954-11-17     1954-11-18
#   incarceration_date   1958-12-14     1961-10-02
#   release_date         1962-12-23     1962-12-24
#   imprisoned_for_days  1470           448
#
# The incarceration date had been set to the December 1958 sentencing after
# his retrial, which is not when he went to prison. Scales was free pending
# litigation for nearly seven years after his arrest: the first conviction
# was reversed by the Supreme Court on October 14, 1957 after the solicitor
# general acknowledged an error over the government withholding FBI witness
# material; he was retried and again sentenced to six years; and the Supreme
# Court affirmed the second conviction 5-4 on June 5, 1961. Only then was he
# ordered to surrender to the United States marshal in New York City, at noon
# on October 2, 1961.
#
# He was released on December 24, 1962 -- Christmas Eve, not the 23rd -- when
# President Kennedy commuted the remainder of the sentence. A COMMUTATION,
# NOT A PARDON: the conviction stayed legally intact, and that distinction is
# now recorded on the case.
#
#   October 2, 1961 to December 24, 1962 = 448 days, fourteen months and
#   twenty-two days, conventionally reported as fifteen months.
#
# The old 1470-day figure came from counting from the 1958 sentencing, four
# years of which he spent at liberty.
#
# The "Years Spent In Prison" list on the profile is derived from the case
# dates, so it corrects itself on save: 1958-1962 becomes 1961-1962.
#
# Also fixed: three possessives in the bio that had lost their apostrophes
# (Communist Party USAs, Khrushchevs, printers union). The rest of the bio,
# including its note-form opening, is left as it is -- it carries facts the
# prose does not (his great-uncle Governor Alfred Moore Scales, UNC Chapel
# Hill, four years in the Army) and nothing in it contradicts the corrected
# dates.
#
# The sentencing date of December 14, 1958 is left alone. It is the retrial
# sentencing, is not contradicted by anything here, and only became misleading
# because it had been copied into the incarceration field as well.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-junius-scales.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "junius-scales")->with("cases")->first();
if (! $p) {
    echo "NOT FOUND: junius-scales\n";
    exit(1);
}

$p->middle_name = "Irving";
$p->in_custody = false;
$p->released = true;
$p->awaiting_trial = false;

$fixes = [
    "Communist Party USAs" => "Communist Party USA’s",
    "Khrushchevs revelations" => "Khrushchev’s revelations",
    "printers union" => "printers’ union",
];
$bio = (string) $p->description;
foreach ($fixes as $from => $to) {
    $bio = str_replace($from, $to, $bio);
}
$p->description = $bio;
$p->save();

$case = $p->cases->first();
if (! $case) {
    echo "NO CASE on junius-scales -- nothing to correct\n";
    exit(1);
}

$case->convicted = "Yes. Convicted at Greensboro in 1955; REVERSED by the Supreme Court on October 14, 1957, after the solicitor general acknowledged an error connected with the government failing to provide relevant FBI witness materials. Retried and reconvicted in 1958, again sentenced to six years. The Supreme Court affirmed 5-4 on June 5, 1961 in Scales v. United States, 367 U.S. 203 — the only conviction under the Smith Act membership clause to stand.";
$case->sentence = "Six years in federal prison. He was free pending litigation and appeals for nearly seven years after his arrest, and was ordered to surrender to the United States marshal in New York City at noon on October 2, 1961, after the Supreme Court affirmed the conviction. He served 448 days — fourteen months and twenty-two days, conventionally reported as fifteen months — at the United States Penitentiary at Lewisburg, Pennsylvania, until President John F. Kennedy commuted the remainder of the sentence on December 24, 1962 and he left Lewisburg that same Christmas Eve. The action was a commutation, not a pardon: the conviction remained legally intact. He was the last Smith Act prisoner released.";
$case->setPartialDate("arrest_date", 1954, 11, 18);
$case->setPartialDate("incarceration_date", 1961, 10, 2);
$case->setPartialDate("release_date", 1962, 12, 24);
$case->save();

$p->refresh()->load("cases");
$case = $p->cases->first();
echo "Junius Irving Scales  [{$p->slug}]\n";
echo "  arrest       ".$case->arrest_date->toDateString()."  (indicted the same day)\n";
echo "  sentenced    ".($case->sentenced_date ? $case->sentenced_date->toDateString() : "-")."  (retrial sentencing, left as found)\n";
echo "  incarcerated ".$case->incarceration_date->toDateString()."  (surrendered to the marshal, New York City)\n";
echo "  released     ".$case->release_date->toDateString()."  (Kennedy commutation)\n";
echo "  imprisoned_for_days = ".($case->imprisoned_for_days ?? "null")."  (expect 448, was 1470)\n";
echo "  years in prison: ".implode(", ", $p->years_in_prison ?: [])."  (expect 1961, 1962)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
