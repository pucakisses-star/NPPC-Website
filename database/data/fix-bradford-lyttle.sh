#!/usr/bin/env bash
#
# Bradford Lyttle -- portrait, date of birth, and the full arrest and
# incarceration chronology.
#
# The record held one undated case for Omaha Action and nothing else,
# for a man arrested repeatedly over fifty-six years. Twelve episodes
# are now recorded, each carrying its own confidence in the case text.
#
# DATE OF BIRTH: November 20, 1927. Day precision.
#
# THE COUNTER WILL LOOK TOO SMALL, AND THAT IS CORRECT. Only five of
# the twelve episodes have both ends documented, so the imprisonment
# total comes to 54 days -- even though his two longest terms, the nine
# months of 1954 and the six months from July 1959, are both in the
# record. Neither has a documented release date, and this database does
# not manufacture one by adding a sentence length to an admission date.
# The terms are stated in the case text; the counter stays honest.
# Resolving the 1954 Bureau of Prisons file and the Omaha discharge
# date would move roughly fifteen months into the total.
#
# THE TWELVE EPISODES, and what each rests on:
#
#   1954              Refusing to cooperate with Selective Service.
#                     Nine months at the U.S. Medical Center for
#                     Federal Prisoners, Springfield, Missouri.
#                     CONFIRMED imprisonment, NO DATES -- neither the
#                     autobiography preview nor the Swarthmore finding
#                     aid gives admission or discharge. Dateless case.
#
#   Jul  8, 1959      Arrested at Omaha Action, Mead, Nebraska, with
#                     Donald Fortenberry; bailed before sentencing.
#   Jul 13, 1959      Sentenced to six months and a $500 fine, taken
#                     into custody the same day. Release unresolved --
#                     approximately December 1959 or January 1960.
#                     Recorded as ONE case: the July 8 arrest is the
#                     arrest date on the sentence row, because he was
#                     bailed in between and a separate row would imply
#                     custody he did not serve. The fine is not custody
#                     and adds nothing to the counter.
#
#   Nov 19-22, 1963   Quebec-to-Cuba Peace Walk, Macon, Georgia.
#                     Nineteen walkers arrested for leafleting, three
#                     or four days served, sentences commuted Nov 22.
#                     PROBABLE for Lyttle personally: contemporary
#                     reports name him as the walks leader but no
#                     booking sheet listing all nineteen has been
#                     found. Recorded with the inference stated.
#
#   Dec 23, 1963 -    First Albany, Georgia imprisonment. CONFIRMED --
#   Jan 16, 1964      the CNVA bulletin names him among fourteen
#                     arrested for continuing the integrated march.
#                     Released in two groups on January 16 and 17; the
#                     bulletin does not say which day was his, so the
#                     EARLIER date is used and the other is noted.
#
#   Jan 27 -          Second Albany imprisonment. STRONGLY SUPPORTED:
#   Feb 22, 1964      the archive holds a jail log spanning these dates
#                     and his own jail letters dated February 3, but
#                     the log is not labelled as his individual
#                     admission-and-release record.
#
#   Feb 15, 1966      Times Square antiwar sit-down, New York. ARREST
#                     CONFIRMED (the papers catalogue both the sit-down
#                     and his account "Arrest and Confinement in the
#                     NYC Jail"); release not published. No release
#                     date, so no days counted.
#
#   Apr 20, 1966      Saigon. Arrested with five other American
#                     pacifists trying to demonstrate near the U.S.
#                     Embassy, driven to the airport and deported to
#                     Hong Kong within hours. The arrest falls in an
#                     April 20-21 window; the 20th is used, matching
#                     the contemporary Associated Press photograph.
#                     Same-day release, so the row counts zero days --
#                     hours, not days, which is correct.
#
#   May 12, 1967      Pentagon civil disobedience; one of twenty-three
#                     taken to federal court in Alexandria, Virginia.
#                     Release not found.
#
#   May  3, 1971      Mayday, Washington D.C. Arrest confirmed by the
#                     Swarthmore archive; a D.C. Superior Court grand
#                     jury reportedly indicted him on an assault charge
#                     on May 13. Release and disposition unresolved.
#
#   Aug 28, 1996      Democratic National Convention protest, Chicago.
#                     Arrested by Federal Protective Service officers
#                     with David Dellinger and nine others during the
#                     Kluczynski Federal Building sit-in. Length not
#                     reported.
#
#   Mar 19, 2005      Chicago anti-Iraq War demonstration. Arrested
#                     after a dispersal order near Oak Street and
#                     Michigan Avenue; ACQUITTED by directed verdict,
#                     the court finding he had not been given adequate
#                     time to disperse. The episode is unusually well
#                     documented because it produced his federal
#                     civil-rights suit. Release time not published.
#
#   Jul  5-6, 2010    Y-12 nuclear weapons complex, Oak Ridge,
#                     Tennessee. Entered federal property with twelve
#                     other antinuclear activists on July 5, held
#                     overnight, before a federal court July 6.
#                     Convicted May 11, 2011 -- which is why some
#                     accounts wrongly date his "last arrest" to 2011.
#                     That confusion is recorded on the case.
#
# DELIBERATELY NOT ENTERED, because the dossier does not support them
# as individual Lyttle custody:
#   - the Lawrenceville and Griffin, Georgia arrests preceding Macon
#     (no dates, no defendant list)
#   - the Boca Chica / Key West arrest (no precise date, June or July
#     1964)
#   - the March 20, 2003 Chicago demonstration, where a federal court
#     placed him at the protest but did not say he was among those
#     arrested
#
# Cases are keyed by a marker in the charges text, so re-running
# updates the same rows rather than adding duplicates.
#
# Run from the repo root:
#   bash database/data/fix-bradford-lyttle.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$p = Prisoner::withoutGlobalScopes()->where("slug", "bradford-lyttle")->with("cases")->first();
if (! $p) {
    echo "NOT FOUND: bradford-lyttle\n";
    exit(1);
}

$p->first_name = "Bradford";
$p->last_name = "Lyttle";
$p->gender = "Male";
$p->in_custody = false;
$p->released = true;
$p->awaiting_trial = false;
$p->setPartialDate("birthdate", 1927, 11, 20);
$p->ideologies = ["Anti-War", "Civil Rights", "Civil Liberties"];
$p->description = "Bradford Lyttle is an American pacifist organizer whose arrests span more than half a century, from draft refusal in the early 1950s to a nuclear weapons plant in 2010. He first went to prison in 1954, serving nine months at the United States Medical Center for Federal Prisoners in Springfield, Missouri for refusing to cooperate with Selective Service. In 1959 he organized Omaha Action, the nonviolent campaign against the intercontinental ballistic missile base at Mead, Nebraska; arrested on July 8 and bailed, he was sentenced on July 13 to six months and a \$500 fine for trespassing and disregarding a court order. He coordinated the Quebec-to-Guantanamo peace walk of 1963 and 1964, which carried the pacifist movement into the segregated South and was jailed repeatedly along the way — at Macon, Georgia in November 1963, and twice at Albany, Georgia, where the walkers insisted on marching integrated through the downtown business district and spent the turn of 1964 in jail. He was arrested at the Times Square antiwar sit-down in February 1966, and that April he and five other American pacifists were seized in Saigon while trying to demonstrate near the United States Embassy and deported to Hong Kong within hours. Arrests followed at the Pentagon in 1967 and in the Mayday actions of 1971. He remained active for decades afterwards, arrested with David Dellinger at the 1996 Democratic National Convention protest in Chicago, acquitted by directed verdict after a 2005 Chicago demonstration against the Iraq war when the court found police had not allowed adequate time to disperse, and arrested again at the Y-12 nuclear weapons complex in Oak Ridge, Tennessee in July 2010, at the age of eighty-two. He was born on November 20, 1927.";
$p->save();

if ($p->photo === null || ! str_contains((string) $p->photo, "bradford-lyttle")) {
    $p->photo = null;
}
$src = database_path("data/photos/nonfree/bradford-lyttle.jpg");
if (is_file($src)) {
    File::ensureDirectoryExists(storage_path("app/public/prisoners"));
    $dest = "prisoners/bradford-lyttle.jpg";
    File::copy($src, storage_path("app/public/".$dest), true);
    touch(storage_path("app/public/".$dest));
    $p->photo = $dest;
    $p->save();
} else {
    echo "  photo file missing: {$src}\n";
}

$institutions = [];
$inst = function (?array $spec) use (&$institutions) {
    if (! $spec) {
        return null;
    }
    $key = $spec[0];
    if (! isset($institutions[$key])) {
        $institutions[$key] = Institution::firstOrCreate(
            ["name" => $spec[0]],
            array_filter(["city" => $spec[1] ?? null, "state" => $spec[2] ?? null]),
        );
    }

    return $institutions[$key]->id;
};

// marker, charges, sentence, arrest, incarceration, release, institution
$cases = [
    [
        "[selective-service-1954]",
        "Refusing to cooperate with Selective Service — draft refusal.",
        "Nine months at the United States Medical Center for Federal Prisoners in Springfield, Missouri. The imprisonment is confirmed by the Swarthmore finding aid to his papers and by his own autobiography, but NEITHER GIVES AN ADMISSION OR DISCHARGE DATE, so no dates are recorded and this term adds nothing to the imprisonment counter. The underlying Bureau of Prisons file should contain both; until it is consulted, nine months of real custody sit outside the total.",
        null, null, null,
        ["United States Medical Center for Federal Prisoners", "Springfield", "Missouri"],
    ],
    [
        "[omaha-action-1959]",
        "Trespassing and disregarding a court order — Omaha Action, the nonviolent campaign against the intercontinental ballistic missile base at Mead, Nebraska. Arrested July 8, 1959 with Donald Fortenberry.",
        "Six months and a \$500 fine, imposed July 13, 1959, with custody beginning the same day. He had been arrested on July 8 and released on bail before sentencing, which is why the arrest and the imprisonment sit on one row rather than two — a separate pre-sentence row would imply custody he did not serve. NO RELEASE DATE IS PUBLICLY DOCUMENTED; the term would have run to about December 1959 or January 1960, but that is arithmetic, not evidence, so the release field is left empty and the six months add nothing to the counter. The fine is not custody.",
        [1959, 7, 8], [1959, 7, 13], null,
        null,
    ],
    [
        "[macon-1963]",
        "Distributing leaflets — Quebec-to-Guantanamo Peace Walk, Macon, Georgia. Nineteen walkers were arrested.",
        "Three or four days, the sentences commuted on November 22, 1963. PROBABLE RATHER THAN PROVEN for Lyttle personally: contemporary reports identify him as the leader of the walk, which makes his inclusion among the nineteen very likely, but no booking sheet naming all nineteen has been found. The three-day span recorded here is the shorter of the two lengths reported.",
        [1963, 11, 19], [1963, 11, 19], [1963, 11, 22],
        null,
    ],
    [
        "[albany-first-1963]",
        "Continuing the integrated march through the downtown business district — Albany, Georgia. Lyttle was one of fourteen arrested.",
        "Held from December 23, 1963. The prisoners were released in two groups, on January 16 and January 17, 1964, and the CNVA bulletin does not say which day was his; the EARLIER date is used here, so the figure is a floor rather than a guess. If he left on the 17th the term was one day longer.",
        [1963, 12, 23], [1963, 12, 23], [1964, 1, 16],
        null,
    ],
    [
        "[albany-second-1964]",
        "Returning to the restricted area to march — second Albany, Georgia imprisonment.",
        "Approximately January 27 to February 22, 1964. STRONGLY SUPPORTED RATHER THAN PROVEN: the archive holds a jail log spanning exactly these dates and Lyttles own jail letters written from Albany on February 3, but the log is not labelled as his individual admission-and-release record.",
        [1964, 1, 27], [1964, 1, 27], [1964, 2, 22],
        null,
    ],
    [
        "[times-square-1966]",
        "Antiwar sit-down in Times Square, New York City.",
        "Arrest confirmed — his papers catalogue both the Times Square sit-down and his own account, “Arrest and Confinement in the NYC Jail”, under this date. The length of the confinement is not published, so no release date is recorded and the episode adds nothing to the counter.",
        [1966, 2, 15], [1966, 2, 15], null,
        null,
    ],
    [
        "[saigon-1966]",
        "Attempting to demonstrate near the United States Embassy in Saigon, South Vietnam, with five other American pacifists.",
        "Arrested, driven to the airport and deported to Hong Kong within hours — custody measured in hours rather than days, which is why this row contributes zero days rather than being incomplete. Sources place the arrest in an April 20-21, 1966 window; April 20 is used, matching the contemporary Associated Press photograph.",
        [1966, 4, 20], [1966, 4, 20], [1966, 4, 20],
        null,
    ],
    [
        "[pentagon-1967]",
        "Antiwar civil disobedience at the Pentagon; one of twenty-three arrested and taken to federal court in Alexandria, Virginia.",
        "Arrest confirmed by contemporary reporting. Neither the hour nor the date of release has been found, so no release date is recorded.",
        [1967, 5, 12], [1967, 5, 12], null,
        null,
    ],
    [
        "[mayday-1971]",
        "Mayday antiwar actions, Washington, D.C.",
        "Arrest confirmed by the Swarthmore archive, which catalogues a Mayday arrest in 1971. A District of Columbia Superior Court grand jury reportedly indicted him on May 13 on an assault charge connected with the demonstrations. Booking duration and final disposition are both unresolved, so no release date is recorded.",
        [1971, 5, 3], [1971, 5, 3], null,
        null,
    ],
    [
        "[dnc-chicago-1996]",
        "Sit-in at the Kluczynski Federal Building during the Democratic National Convention protest, Chicago. Arrested by Federal Protective Service officers with David Dellinger and nine others.",
        "Arrest confirmed. Public reporting does not state how long he was held, so no release date is recorded.",
        [1996, 8, 28], [1996, 8, 28], null,
        null,
    ],
    [
        "[chicago-iraq-2005]",
        "Disorderly conduct — anti-Iraq War demonstration near Oak Street and Michigan Avenue, Chicago, after a police dispersal order.",
        "ACQUITTED: the court entered a directed verdict of not guilty, finding that he had not been given adequate time to disperse. The arrest is unusually well documented because it produced his federal civil-rights lawsuit, but the release time is not in the published opinions, so no release date is recorded and the apparently brief custody adds nothing to the counter.",
        [2005, 3, 19], [2005, 3, 19], null,
        null,
    ],
    [
        "[y12-2010]",
        "Entering federal property at the Y-12 nuclear weapons complex, Oak Ridge, Tennessee, with twelve other antinuclear activists.",
        "Arrested July 5, 2010, held overnight and brought before a federal court on July 6. He was convicted on May 11, 2011, which is why accounts describing his last arrest as taking place in 2011 are confusing the year of the prosecution with the year of the arrest. He was eighty-two at the time.",
        [2010, 7, 5], [2010, 7, 5], [2010, 7, 6],
        null,
    ],
];

foreach ($cases as [$marker, $charges, $sentence, $arrest, $incarceration, $release, $institution]) {
    $case = $p->cases->first(fn ($c) => str_contains((string) $c->charges, $marker));
    if (! $case && $marker === "[omaha-action-1959]") {
        // The single pre-existing row is the Omaha Action one; adopt it
        // rather than leaving an unmarked duplicate behind.
        $case = $p->cases->first(fn ($c) => ! preg_match("/\[[a-z0-9-]+\]/", (string) $c->charges));
    }
    $case = $case ?? $p->cases()->make([]);
    $case->prisoner_id = $p->id;
    $case->charges = $marker." ".$charges;
    $case->sentence = $sentence;
    $case->institution_id = $inst($institution);
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
echo "\nBradford Lyttle  [{$p->slug}]\n";
echo "  born ".($p->formatPartialDate("birthdate") ?: "-")."   age ".($p->age ?? "-")."   photo ".($p->photo ?: "(none)")."\n";
echo "  cases: ".$p->cases->count()."\n";
$total = 0;
foreach ($p->cases->sortBy("incarceration_date") as $c) {
    preg_match("/\[([a-z0-9-]+)\]/", (string) $c->charges, $m);
    $total += (int) $c->imprisoned_for_days;
    echo "   ".str_pad($m[1] ?? "?", 24)
        ." inc ".str_pad(optional($c->incarceration_date)->toDateString() ?: "-", 12)
        ." rel ".str_pad(optional($c->release_date)->toDateString() ?: "-", 12)
        ." days ".($c->imprisoned_for_days ?? "null")."\n";
}
echo "  TOTAL {$total} days  (expect 54: 3 + 24 + 26 + 0 + 1 — the 1954 and 1959 terms have no documented release and are excluded by design)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
