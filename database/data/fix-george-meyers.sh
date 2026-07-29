#!/usr/bin/env bash
#
# George Aloysius Meyers -- the missing contempt imprisonment, the real
# custody dates, life dates and full name.
#
# The record had one case carrying nothing but a sentencing date, so a
# man who served more than three years showed an imprisonment counter of
# zero, and the SEPARATE THIRTY-DAY CONTEMPT IMPRISONMENT was missing
# entirely.
#
# TWO DISTINCT PERIODS OF CUSTODY, each its own case row (the profile
# sums across cases):
#
#   CONTEMPT     Apr  2, 1952  Judge W. Calvin Chesnut held him in
#                              contempt for refusing to name other
#                              members of the Communist Party
#                              Maryland-District of Columbia leadership
#                Apr  4, 1952  thirty days imposed, separate from and
#                              consecutive to the four-year term; he
#                              began serving it AT ONCE, at the federal
#                              institution at Petersburg, Virginia,
#                              while the Smith Act sentence itself was
#                              stayed pending appeal. Contemporary
#                              reporting confirms he was serving it by
#                              April 10.
#                May  4, 1952  released, approximately -- the sources
#                              give the length as thirty days rather
#                              than a discharge date, so the end is
#                              computed from the start and flagged as
#                              approximate in the case text.
#                              = 30 days
#
#   SMITH ACT    Aug  7, 1951  arrested by the FBI as one of six
#                              Maryland-Washington Communist Party
#                              officials; RELEASED ON PRETRIAL BOND, so
#                              no custody is recorded from the arrest
#                Apr  1, 1952  convicted by a Baltimore federal jury of
#                              conspiracy to violate the Smith Act
#                Apr  4, 1952  sentenced: four years and a \$1,000 fine
#                Jul 31, 1952  Fourth Circuit affirms; rehearing denied
#                              September 8
#                late 1952     Supreme Court denies certiorari, then
#                              denies rehearing
#                Jan 27, 1953  surrendered to the U.S. marshal and
#                              returned to Petersburg
#                May 19, 1956  released
#                              = 1,208 days = 3 years, 3 months, 22 days
#
#   Combined: 1,238 days, about 3 years 4 months 22 days.
#
# THE FINE IS NOT CUSTODY and adds nothing to the counter.
#
# LIFE DATES are YEAR PRECISION: born 1912 in Lonaconing, Maryland,
# died 1999. No source located gives the day or month of either, so
# neither is invented and the profile will print just the years.
#
# NAME: George Aloysius Meyers. The middle name is added; the published
# slug george-meyers is left alone, since a slug is only an address.
#
# NO PHOTOGRAPH IS ATTACHED. The dossier notes that documented
# photographs exist, including a circa-1950 portrait, but none was
# supplied with it and none is invented here. Send the image or a URL
# and it can be cropped and attached in the usual way.
#
# Cases are keyed by markers in the charges text, so re-running updates
# the same two rows rather than adding more. Run from the repo root:
#   bash database/data/fix-george-meyers.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;

// The Prisoner model regenerates the slug whenever the name changes, so
// the first run of this script (adding Aloysius) moves him from
// george-meyers to george-aloysius-meyers. Look up both, so a re-run
// finds him instead of failing.
$p = Prisoner::withoutGlobalScopes()
    ->whereIn("slug", ["george-meyers", "george-aloysius-meyers"])
    ->with("cases")->first();
if (! $p) {
    echo "NOT FOUND: george-meyers / george-aloysius-meyers\n";
    exit(1);
}

$p->name = "George Aloysius Meyers";
$p->first_name = "George";
$p->middle_name = "Aloysius";
$p->last_name = "Meyers";
$p->race = "White";
$p->gender = "Male";
$p->state = "Maryland";
$p->in_custody = false;
$p->released = true;
$p->awaiting_trial = false;
$p->setPartialDate("birthdate", 1912);
$p->setPartialDate("death_date", 1999);
$p->ideologies = ["Communism", "Labor Organizing", "Civil Liberties"];
$p->description = "George Aloysius Meyers was a textile-union organizer and Communist Party leader imprisoned under the Smith Act. Born in 1912 in Lonaconing, Maryland, he helped organize thousands of workers at the Celanese textile plant near Cumberland and became president of the local textile union; in 1941 he was elected president of the Maryland–District of Columbia CIO council. He later served as chairman of the Communist Party in Maryland and as a national CPUSA labor official. On August 7, 1951 the FBI arrested him as one of six Maryland and Washington Communist Party officials charged under the Smith Act, which made it a crime to advocate the overthrow of the government and was used against Communist Party members throughout the McCarthy years. Released on pretrial bond, he was convicted with the other five by a Baltimore federal jury on April 1, 1952. The following day Judge W. Calvin Chesnut held him in contempt for refusing to identify other members of the party leadership, and on April 4 sentenced him to four years and a \$1,000 fine on the Smith Act count plus a separate thirty days for the contempt. He served the contempt term immediately, at the federal institution at Petersburg, Virginia, while the longer sentence was stayed pending appeal. The Fourth Circuit affirmed on July 31, 1952 and denied rehearing on September 8; after the Supreme Court declined to review the case he surrendered to the United States marshal on January 27, 1953 and returned to Petersburg, where he remained until his release on May 19, 1956 — three years, three months and twenty-two days, and about three years and four and a half months counting the contempt. He died in 1999.";
$p->save();

$petersburg = Institution::firstOrCreate(
    ["name" => "Federal Reformatory, Petersburg"],
    ["city" => "Petersburg", "state" => "Virginia"],
);

$rows = [
    [
        "[contempt-1952]",
        "Criminal contempt of court — refusing to identify other members of the Communist Party leadership for Maryland and the District of Columbia. Judge W. Calvin Chesnut held him in contempt on April 2, 1952, the day after the Smith Act verdict.",
        "Thirty days, imposed April 4, 1952 as a term separate from and consecutive to the four-year Smith Act sentence. He began serving it at once, at the federal institution at Petersburg, Virginia, while the Smith Act sentence was stayed pending appeal; contemporary reporting confirms he was serving the contempt term by April 10. THE RELEASE DATE IS COMPUTED, NOT DOCUMENTED: the sources give the length as thirty days rather than a discharge date, so about May 4, 1952 is the arithmetic end of a term beginning April 4 and should be treated as approximate.",
        [1952, 4, 4], [1952, 5, 4],
        "Judge W. Calvin Chesnut",
        null,
    ],
    [
        "[smith-act-1952]",
        "Conspiracy to violate the Smith Act — advocating the overthrow of the government. One of six Maryland and Washington Communist Party officials arrested by the FBI on August 7, 1951 and tried together in federal court in Baltimore.",
        "Four years and a \$1,000 fine, imposed April 4, 1952 after conviction by a Baltimore federal jury on April 1. He was released on pretrial bond after the August 7, 1951 arrest and remained at liberty on the Smith Act count while the case was appealed, which is why no custody is recorded from the arrest. The Fourth Circuit affirmed on July 31, 1952 and denied rehearing on September 8; the Supreme Court then denied certiorari and denied a petition for rehearing. He surrendered to the United States marshal on January 27, 1953 and was returned to the federal institution at Petersburg, Virginia, where he served until May 19, 1956 — 1,208 days, three years three months and twenty-two days of the four years. The fine is not custody and adds nothing to the imprisonment total.",
        [1953, 1, 27], [1956, 5, 19],
        "Judge W. Calvin Chesnut",
        [1951, 8, 7],
    ],
];

foreach ($rows as [$marker, $charges, $sentence, $incarceration, $release, $judge, $arrest]) {
    $case = $p->cases->first(fn ($c) => str_contains((string) $c->charges, $marker));
    if (! $case && $marker === "[smith-act-1952]") {
        $case = $p->cases->first(fn ($c) => ! preg_match("/\[[a-z0-9-]+\]/", (string) $c->charges));
    }
    $case = $case ?? $p->cases()->make([]);
    $case->prisoner_id = $p->id;
    $case->charges = $marker." ".$charges;
    $case->sentence = $sentence;
    $case->judge = $judge;
    $case->institution_id = $petersburg->id;
    $case->convicted = $marker === "[smith-act-1952]"
        ? "Yes — convicted April 1, 1952 by a federal jury in Baltimore. Affirmed by the Fourth Circuit on July 31, 1952, rehearing denied September 8; the Supreme Court denied certiorari and then denied rehearing."
        : "Yes — held in contempt April 2, 1952 by Judge W. Calvin Chesnut.";
    if ($marker === "[smith-act-1952]") {
        $case->setPartialDate("sentenced_date", 1952, 4, 4);
    }
    if ($arrest) {
        $case->setPartialDate("arrest_date", ...$arrest);
    } else {
        $case->arrest_date = null;
    }
    $case->setPartialDate("incarceration_date", ...$incarceration);
    $case->setPartialDate("release_date", ...$release);
    $case->save();
}

$p->refresh()->load("cases");
echo "\n{$p->name}  [{$p->slug}]\n";
echo "  born ".($p->formatPartialDate("birthdate") ?: "-")."   died ".($p->formatPartialDate("death_date") ?: "-")."   age ".($p->age ?? "-")."  (expect 1912, 1999, 87)\n";
$total = 0;
foreach ($p->cases->sortBy("incarceration_date") as $c) {
    preg_match("/\[([a-z0-9-]+)\]/", (string) $c->charges, $m);
    $total += (int) $c->imprisoned_for_days;
    echo "  ".str_pad($m[1] ?? "?", 18)
        ." inc ".str_pad(optional($c->incarceration_date)->toDateString() ?: "-", 12)
        ." rel ".str_pad(optional($c->release_date)->toDateString() ?: "-", 12)
        ." days ".($c->imprisoned_for_days ?? "null")."\n";
}
echo "  TOTAL {$total} days  (expect 1238 = 30 contempt + 1208 Smith Act, about 3 years 4 months 22 days; was 0)\n";
echo "  photo ".($p->photo ?: "(none — no image was supplied with the dossier)")."\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
