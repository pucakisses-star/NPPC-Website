#!/usr/bin/env bash
#
# Jane Speed de Andreu -- portrait and five documented custody episodes.
#
# The record held a single dateless Smith Act case, so a woman jailed on
# at least five occasions across twenty-one years showed an imprisonment
# counter of zero and no dates at all.
#
# FIVE EPISODES, each carrying its confidence in the case text:
#
#   May  1, 1933    Birmingham. Arrested after addressing the
#   ~Jun 23, 1933   interracial May Day demonstration; convicted of
#                   disorderly conduct, speaking without a permit and
#                   violating the ordinances against unsegregated public
#                   assemblies. Offered a fine, she CHOSE JAIL and
#                   served 53 days. No discharge sheet has surfaced, so
#                   the release is CALCULATED from the 53 days: May 1
#                   plus 53 is June 23, 1933, and the sources give the
#                   date as about June 22 or 23. The older figure of 48
#                   days is superseded by the stronger scholarly account
#                   of 53.
#                   = 53 days
#
#   Sep  7, 1935    New York. Arrested with Julia Church Kolar after
#                   entering the Italian Consulate to protest
#                   Mussolinis planned invasion of Ethiopia. Convicted,
#                   but the sentence was SUSPENDED, so no prison term
#                   followed; the length of the booking custody is not
#                   stated. No release date, so nothing is counted.
#
#   Jul 14, 1950    Santurce, Puerto Rico. Arrested with Manuel Arroyo
#                   Zeppenfeldt and Ana Livia Cordero outside the New
#                   York Department Stores while collecting signatures
#                   against use of the atomic bomb; the account says
#                   they were released shortly afterwards. Recorded as
#                   released the same day, so the row counts ZERO days
#                   -- hours rather than days, which is the fact and not
#                   a gap.
#
#   Mar  7, 1954    Arecibo Womens Jail. Held three weeks WITHOUT BEING
#   ~Mar 28, 1954   FORMALLY CHARGED in the roundup that followed the
#                   Capitol shooting: Munoz Marin returned to Puerto
#                   Rico on March 5, the Nationalists including Albizu
#                   Campos were taken at dawn on March 6, and the
#                   Communists including Speed at the next dawn, March
#                   7. The release is CALCULATED from the three weeks.
#                   One secondary account places the raid at a dawn in
#                   April 1954 instead; the day-by-day March chronology
#                   is the more persuasive and the conflict is recorded
#                   on the case.
#                   = 21 days
#
#   Oct 20, 1954    Federal Smith Act roundup, with her husband Cesar
#                   Andreu Iglesias; indicted October 27. Bail was set
#                   around \$25,000 to \$28,000 and some defendants
#                   stayed in for months, but no source says whether she
#                   was among them. RELEASE DATE UNKNOWN, so nothing is
#                   counted.
#
#                   1958 IS NOT HER RELEASE DATE. That is when the
#                   prosecution was withdrawn and archived, and it is no
#                   evidence at all that she was held continuously until
#                   then. The case text says so explicitly, because a
#                   1954-to-1958 span is exactly the mistake this record
#                   invites.
#
#   Countable total: 74 days (53 + 0 + 21). The two episodes with no
#   documented release contribute nothing, which is correct rather than
#   missing. The unresolved figure is her release after the October 1954
#   arrest, which needs a federal docket, bail bond or detention record.
#
# THE PHOTOGRAPH is 225x229, the largest version the source serves. It
# is used at that size rather than upscaled.
#
# Cases are keyed by markers in the charges text, so re-running updates
# the same rows. The pre-existing dateless row is adopted as the Smith
# Act case. Run from the repo root:
#   bash database/data/fix-jane-speed-de-andreu.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$p = Prisoner::withoutGlobalScopes()->where("slug", "jane-speed-de-andreu")->with("cases")->first();
if (! $p) {
    echo "NOT FOUND: jane-speed-de-andreu\n";
    exit(1);
}

$p->first_name = "Jane";
$p->last_name = "Speed";
$p->gender = "Female";
$p->race = "White";
$p->in_custody = false;
$p->released = true;
$p->awaiting_trial = false;
$p->ideologies = ["Communism", "Civil Rights", "Anti-War", "Civil Liberties"];
$p->description = "Jane Speed de Andreu was an Alabama-born Communist organizer who was jailed on at least five occasions between 1933 and 1954, in Birmingham, New York and Puerto Rico. She came from an old Alabama family and worked in the sharecroppers and anti-Jim Crow struggles of the 1930s South. On May 1, 1933 she addressed the interracial May Day demonstration in Birmingham and was arrested; convicted of disorderly conduct, of speaking without a permit and of violating the city ordinances forbidding unsegregated public assemblies, she was offered a fine and chose jail instead, serving fifty-three days. In September 1935 she and Julia Church Kolar were arrested after entering the Italian Consulate in New York to protest Mussolinis planned invasion of Ethiopia; both were convicted and given suspended sentences. She later moved to Puerto Rico and married the Communist writer Cesar Andreu Iglesias. Police arrested her in Santurce on July 14, 1950, with Manuel Arroyo Zeppenfeldt and Ana Livia Cordero, while they gathered signatures on a petition against use of the atomic bomb. In the roundup that followed the March 1954 attack on the United States Capitol she was seized at dawn on March 7 and held three weeks in the Arecibo Womens Jail without ever being formally charged. On October 20, 1954 she was arrested again, with her husband, in the federal Smith Act roundup, and was indicted a week later as the only continental defendant among the eleven charged in the first Smith Act prosecution ever brought in Puerto Rico. The case was tried in San Juan in 1956 in English, a language most of the defendants could not follow; the prosecution was withdrawn and archived in 1958 without a conviction against her.";
$p->save();

$src = database_path("data/photos/nonfree/jane-speed-de-andreu.jpg");
if (is_file($src)) {
    File::ensureDirectoryExists(storage_path("app/public/prisoners"));
    $dest = "prisoners/jane-speed-de-andreu.jpg";
    File::copy($src, storage_path("app/public/".$dest), true);
    touch(storage_path("app/public/".$dest));
    $p->photo = $dest;
    $p->save();
} else {
    echo "  photo file missing: {$src}\n";
}

$arecibo = Institution::firstOrCreate(
    ["name" => "Arecibo Women".chr(39)."s Jail"],
    ["city" => "Arecibo", "state" => "Puerto Rico"],
);

// marker, charges, sentence, arrest, incarceration, release, institution_id
$rows = [
    [
        "[birmingham-1933]",
        "Disorderly conduct, speaking without a permit, and violating the Birmingham ordinances prohibiting unsegregated public assemblies — for addressing the interracial May Day demonstration of May 1, 1933.",
        "Offered a fine, she chose jail instead and served fifty-three days. NO DISCHARGE RECORD HAS SURFACED: the release date here is calculated from the length, May 1 plus fifty-three days giving June 23, 1933, and the sources put it at about June 22 or 23. An older account giving forty-eight days is superseded by the stronger scholarly figure of fifty-three.",
        [1933, 5, 1], [1933, 5, 1], [1933, 6, 23], null,
    ],
    [
        "[italian-consulate-1935]",
        "Arrested with Julia Church Kolar after entering the Italian Consulate in New York City to protest against Mussolini’s planned invasion of Ethiopia.",
        "Convicted, but the sentence was SUSPENDED and no prison term followed. She was held only through booking or arraignment and the length of that custody is not stated in the available reports, so no release date is recorded and this episode adds nothing to the imprisonment total.",
        [1935, 9, 7], [1935, 9, 7], null, null,
    ],
    [
        "[santurce-1950]",
        "Arrested by Puerto Rican police with Manuel Arroyo Zeppenfeldt and Ana Livia Cordero outside the New York Department Stores in Santurce, while collecting signatures on a petition opposing use of the atomic bomb.",
        "Brief detention — the account records that those arrested were released shortly afterwards, so the release is entered as the same day and this row counts zero days. That is the fact of a custody measured in hours, not a missing figure.",
        [1950, 7, 14], [1950, 7, 14], [1950, 7, 14], null,
    ],
    [
        "[arecibo-1954]",
        "Detained without charge in the crackdown that followed the March 1, 1954 attack on the United States Capitol. Governor Munoz Marin returned to Puerto Rico on March 5; Pedro Albizu Campos and other Nationalists were taken at dawn on March 6, and the Communists, Speed among them, at the next dawn.",
        "Three weeks in the Arecibo Women’s Jail, SHE WAS NEVER FORMALLY CHARGED. The release date is calculated from the three weeks she described rather than confirmed by a jail register. A secondary account places the raid at a dawn in April 1954 instead of March; the detailed day-by-day chronology of the March crackdown is the more persuasive, and March 7 is used here with the conflict recorded rather than hidden.",
        [1954, 3, 7], [1954, 3, 7], [1954, 3, 28], "arecibo",
    ],
    [
        "[smith-act-1954]",
        "Smith Act — conspiracy to advocate the overthrow of the government. Arrested in the federal roundup of October 20, 1954 with her husband Cesar Andreu Iglesias and indicted on October 27, one of eleven defendants in the first Smith Act prosecution ever brought in Puerto Rico, and the only continental among them.",
        "Pretrial custody of unresolved length. Bail was set extraordinarily high — roughly \$25,000 to \$28,000 depending on the defendant — and some of the eleven consequently stayed in jail for months, but no source establishes whether she was among them, so NO RELEASE DATE IS RECORDED and this episode adds nothing to the imprisonment total. The case was tried in San Juan in 1956 in English, which most of the defendants could not follow, and the prosecution was withdrawn and archived in 1958 with no conviction located against her. 1958 IS NOT A RELEASE DATE: it marks the end of the prosecution and is no evidence that she was held until then. Her release after the October 1954 arrest is the one date this record still needs, and it will come from a federal docket, a bail bond or a detention register.",
        [1954, 10, 20], [1954, 10, 20], null, null,
    ],
];

foreach ($rows as [$marker, $charges, $sentence, $arrest, $incarceration, $release, $inst]) {
    $case = $p->cases->first(fn ($c) => str_contains((string) $c->charges, $marker));
    if (! $case && $marker === "[smith-act-1954]") {
        $case = $p->cases->first(fn ($c) => ! preg_match("/\[[a-z0-9-]+\]/", (string) $c->charges));
    }
    $case = $case ?? $p->cases()->make([]);
    $case->prisoner_id = $p->id;
    $case->charges = $marker." ".$charges;
    $case->sentence = $sentence;
    $case->institution_id = $inst === "arecibo" ? $arecibo->id : null;
    $case->convicted = match ($marker) {
        "[birmingham-1933]" => "Yes — convicted of disorderly conduct, speaking without a permit and violating the ordinances against unsegregated assemblies.",
        "[italian-consulate-1935]" => "Yes — convicted, sentence suspended.",
        "[arecibo-1954]" => "No — held three weeks without ever being formally charged.",
        "[smith-act-1954]" => "No conviction located. Tried in San Juan in 1956; the prosecution was withdrawn and archived in 1958.",
        default => null,
    };
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
echo "  photo ".($p->photo ?: "(none)")."\n";
$total = 0;
foreach ($p->cases->sortBy("incarceration_date") as $c) {
    preg_match("/\[([a-z0-9-]+)\]/", (string) $c->charges, $m);
    $total += (int) $c->imprisoned_for_days;
    echo "  ".str_pad($m[1] ?? "?", 24)
        ." inc ".str_pad(optional($c->incarceration_date)->toDateString() ?: "-", 12)
        ." rel ".str_pad(optional($c->release_date)->toDateString() ?: "-", 12)
        ." days ".($c->imprisoned_for_days ?? "null")."\n";
}
echo "  TOTAL {$total} days  (expect 74 = 53 Birmingham + 0 Santurce + 21 Arecibo; the 1935 and Smith Act episodes have no documented release)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
