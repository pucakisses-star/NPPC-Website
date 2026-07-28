#!/usr/bin/env bash
#
# Carl Paivio -- expanded and corrected record.
#
# WHAT THE RECORD HELD
#   Two sentences of bio, no birth or death date, no ideologies, and a case
#   with no arrest date, no incarceration date, no sentence and a release
#   stored as 1923-01-01 at day precision -- a defaulted January 1 the sources
#   never claimed.
#
# THE CASE
#   The Lusk Committee raided the New York IWW headquarters on June 21, 1919
#   and seized copies of the Finnish-language paper Luokkataistelu ("Class
#   Struggle"), focusing on an article advocating mass action, sabotage and
#   revolutionary violence. Gust Alonen was arrested in New York on August 7;
#   letters found with him placed Paivio in Detroit, where New York and
#   Michigan officers arrested him on August 13, 1919. Returned to New York and
#   held in The Tombs, bail set at $25,000 and reduced to $10,000, reported at
#   the time as beyond his means -- so he was in custody from the arrest.
#
#   Jury selection opened October 6, 1919 and testimony October 9, before a
#   specially selected jury drawn largely from businessmen and professionals;
#   defence counsel Walter Nelles objected that such a panel would be
#   prejudiced against radicals. Neither defendant was accused of a bombing, an
#   assault or any violent act -- the case rested on responsibility for printed
#   matter advocating future revolutionary violence.
#
#   Convicted October 25, sentenced October 28, 1919 to four to eight years at
#   hard labour, with Judge Weeks recommending deportation afterwards. They
#   were the first men convicted under New York’s 1902 criminal-anarchy law.
#   The deportation order was never carried out.
#
# WHERE HE WAS HELD
#   The Tombs before trial, then Sing Sing on the state sentence, then Auburn
#   -- an April 1920 IWW publication has him at Auburn and Alonen at Clinton --
#   and possibly Clinton later, since some accounts have him released from
#   there while Finnish memorial accounts mention only Sing Sing and Auburn.
#   The case row can hold one institution, so it carries Sing Sing, where the
#   state sentence began, and the whole progression is written into the
#   sentence text.
#
# THE RELEASE DATE, AND WHAT IT DOES TO THE COUNTER
#   Recorded as 1923 at YEAR precision, per the supplied record: he was
#   released during 1923 and no source establishes the day. Year precision
#   stores January 1 internally, so the counter will read about 3 years
#   4 months rather than the "approximately four years" in the text. The
#   alternative is worse -- asserting a day nobody has found.
#
#   There is reported Finnish-American newspaper coverage from July 1923. To
#   record that instead, at month precision, which gives about 3 years
#   10 months and sits much closer to the four years:
#
#     RELEASE_JULY_1923=1 bash database/data/fix-carl-paivio.sh
#
#   Off by default.
#
# A SECOND CASE IS ADDED for the postwar Ellis Island immigration detentions,
# with no dates -- they are documented as repeated but undated. His health
# broke down there and he died on April 14, 1952 before deportation. One later
# congressional transcript gives April 21; April 14 is the better-supported
# date and is what goes in.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-carl-paivio.sh
#   RELEASE_JULY_1923=1 bash database/data/fix-carl-paivio.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

RELEASE_JULY_1923="${RELEASE_JULY_1923:-0}" php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;

$july = getenv("RELEASE_JULY_1923") === "1";

$matches = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->where("slug", "carl-paivio")
        ->orWhereRaw("LOWER(name) IN (?, ?)", ["carl paivio", "karl einar paiviö"]))
    ->with("cases")
    ->get();

if ($matches->isEmpty()) { echo "NOT FOUND: Carl Paivio\n"; exit(1); }
if ($matches->count() > 1) {
    echo "ABORT: {$matches->count()} records match. Refusing to guess:\n";
    foreach ($matches as $m) { echo "  {$m->slug}  {$m->name}\n"; }
    exit(1);
}

$p = $matches->first();

echo "BEFORE\n";
echo "  born=".($p->birthdate ? $p->birthdate->toDateString() : "-")
    ."  died=".($p->death_date ? $p->death_date->toDateString() : "-")
    ."  ideologies=".(implode(", ", $p->ideologies ?: []) ?: "(none)")."\n";
foreach ($p->cases as $c) {
    echo "  case  arr=".($c->arrest_date ? $c->arrest_date->toDateString() : "-")
        ."  inc=".($c->incarceration_date ? $c->incarceration_date->toDateString() : "-")
        ."  rel=".($c->release_date ? $c->release_date->toDateString() : "-")
        ."  (".$c->datePrecisionFor("release_date")." precision)"
        ."  days=".($c->imprisoned_for_days ?? "null")."\n";
}

// ---- the man ---------------------------------------------------------------
$p->first_name = "Carl";
$p->last_name = "Paivio";
$p->aka = "Karl Einar Päiviö";
$p->gender = "Male";
$p->state = "New York";
$p->era = "1910s";
$p->ideologies = ["Anarchism", "Communism", "Labor Organizing", "Immigrant Rights"];
$p->affiliation = [
    "Industrial Workers of the World (IWW)",
    "International Workers Order",
    "Finnish America Mutual Aid Society",
];
$p->setPartialDate("birthdate", 1893, 11, 23);
$p->setPartialDate("death_date", 1952, 4, 14);
$p->in_custody = false;
$p->awaiting_trial = false;
$p->released = true;
$p->description = "Carl Paivio, born Karl Einar Päiviö on November 23, 1893 at Töysä in Finland, was a Finnish-American editor, lecturer and labor organizer and a member of the Industrial Workers of the World. He edited the Finnish-language Bronx paper Luokkataistelu — Class Struggle. After the Lusk Committee raided the New York IWW headquarters on June 21, 1919 and seized copies of the paper, investigators fixed on an article advocating mass action, sabotage and revolutionary violence. His co-editor Gust Alonen was arrested in New York on August 7; letters found with Alonen placed Paivio in Detroit, where New York and Michigan officers arrested him on August 13, 1919. He was returned to New York and held in The Tombs on bail of \$25,000, later reduced to \$10,000 and still reported as beyond his means. Both men were prosecuted under New York’s 1902 criminal-anarchy law. Neither was accused of a bombing, an assault or any other violent act; the case rested almost entirely on their alleged responsibility for printed matter advocating future revolutionary violence, tried before a specially selected jury of businessmen and professionals that defence counsel Walter Nelles argued would be prejudiced against radicals. Convicted on October 25 and sentenced on October 28, 1919, they were the first men convicted under the statute and each received four to eight years at hard labour, with the judge recommending deportation afterwards. Paivio served at Sing Sing and Auburn and may later have been moved to Clinton, and was released during 1923 after roughly four years. The deportation order was never carried out. Afterwards he moved from the anarchist wing of the IWW toward the Communist movement and became a prominent Finnish-American organizer, lecturer and educator, later national secretary of the Finnish America Mutual Aid Society and active in the International Workers Order. During the postwar anti-communist campaign immigration authorities repeatedly detained him at Ellis Island while seeking to deport him; his health broke down in detention and he died in New York on April 14, 1952, before the deportation could be completed.";
$p->save();

// ---- 1919 criminal-anarchy case -------------------------------------------
$sing = Institution::firstOrCreate(
    ["name" => "Sing Sing Prison"],
    ["city" => "Ossining", "state" => "New York"],
);

$c = $p->cases->first(fn ($x) => stripos((string) $x->charges, "anarchy") !== false)
    ?? $p->cases->first()
    ?? $p->cases()->create([]);
$c->institution_id = $sing->id;
$c->charges = "Criminal anarchy, New York Penal Law of 1902 — for editing and distributing the Finnish-language IWW paper Luokkataistelu, which advocated mass action, sabotage and revolutionary violence. No violent act was alleged.";
$c->plead = "Not guilty";
$c->convicted = "Yes — convicted October 25, 1919, the first conviction obtained under New York’s criminal-anarchy statute; tried with co-editor Gust Alonen before a specially selected jury.";
$c->judge = "Bartow S. Weeks";
$c->sentence = "Four to eight years at hard labour, with the judge recommending deportation after the sentence — an order never carried out. Arrested in Detroit on August 13, 1919 and held in The Tombs through the trial, bail being set at \$25,000 and reduced to \$10,000, still beyond his means. He went to Sing Sing on the state sentence and was at Auburn by the spring of 1920, per an April 1920 IWW publication that placed Alonen at Clinton; some later accounts have Paivio released from Clinton, so a further transfer is possible. Released during 1923 after approximately four years. No source establishes the day of release, so it is recorded at year precision and the counter therefore reads short of four years.";
$c->setPartialDate("arrest_date", 1919, 8, 13);
$c->setPartialDate("incarceration_date", 1919, 8, 13);
$c->setPartialDate("sentenced_date", 1919, 10, 28);
if ($july) {
    $c->setPartialDate("release_date", 1923, 7);
} else {
    $c->setPartialDate("release_date", 1923);
}
$c->save();

// ---- postwar Ellis Island detentions ---------------------------------------
$ellis = Institution::firstOrCreate(
    ["name" => "Ellis Island Immigration Station"],
    ["city" => "New York", "state" => "New York"],
);

$d = $p->cases()->where("charges", "like", "%Ellis Island%")->first()
    ?? $p->cases()->create([]);
$d->institution_id = $ellis->id;
$d->charges = "Immigration detention at Ellis Island pending deportation, during the postwar anti-communist campaign — held repeatedly on the strength of his Communist associations.";
$d->convicted = "No — a deportation proceeding, not a criminal charge. He died before it could be completed.";
$d->sentence = "Repeatedly detained at Ellis Island in the years before his death. A Finnish-American memorial account says the prolonged detention badly damaged his health. The dates of the individual detentions are not documented, so none are recorded and this case adds no days to the total.";
$d->save();

// ---- receipt ---------------------------------------------------------------
$p->refresh()->load("cases.institution");
echo "\nAFTER\n";
echo "  {$p->name}  [{$p->slug}]  AKA {$p->aka}\n";
echo "  born ".$p->formatPartialDate("birthdate")."   died ".$p->formatPartialDate("death_date")."   age {$p->age}\n";
echo "  ideologies: ".implode(", ", $p->ideologies)."\n";
$total = 0;
foreach ($p->cases as $c) {
    $total += (int) $c->imprisoned_for_days;
    echo "  case  arr=".str_pad((string) ($c->formatPartialDate("arrest_date") ?: "-"), 14)
        ." inc=".str_pad((string) ($c->formatPartialDate("incarceration_date") ?: "-"), 14)
        ." rel=".str_pad((string) ($c->formatPartialDate("release_date") ?: "-"), 14)
        ." days=".($c->imprisoned_for_days ?? "null")
        ."  ".($c->institution->name ?? "-")."\n";
}
echo "  counter: {$total} days";
if (! $july) { echo "   (year-precision release; RELEASE_JULY_1923=1 gets closer to the reported four years)"; }
echo "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
