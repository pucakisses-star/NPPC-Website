#!/usr/bin/env bash
#
# The Trumbles of Rains County, Texas -- unpick the "R. Trumble" amalgamation
# and record the three men properly.
#
# THE MISTAKE
#   "R. Trumble" is not a person. The source collapsed two members of the same
#   family who drew identical sentences and were paroled on the same day. Its
#   dates were wrong in both directions: it gave September 20, 1918 as the day
#   he entered Atlanta -- that was the conviction and sentencing date -- and
#   the bio then claimed continuous imprisonment from September 1918 to
#   December 1920, which is longer than the two-year sentence and ignores the
#   fifteen months the men spent at liberty on appeal.
#
# THE CASE
#   Anti-draft meetings in Rains County in the summer of 1917, called after
#   local farmers complained their draft quota was unfairly high. At a meeting
#   on July 29 participants allegedly voted that draft-age men should resist
#   induction and older residents should help them; a few afterwards bought
#   small-calibre Winchester sporting rifles. Rumours followed that they meant
#   to raid stores, burn Emory, form armed camps and attack officials. None of
#   it happened -- no town attacked, no encampment, no arms cache, no
#   resistance to arrest -- but federal and local officers arrested about fifty
#   people in raids beginning the night of August 8, 1917. Forty-six were
#   released on individual \$2,000 bonds.
#
#   The trial opened at Tyler on September 9, 1918 and ran about ten days;
#   twenty-three were convicted. The Fifth Circuit affirmed on November 25,
#   1919, holding that abandoning the planned resistance did not erase criminal
#   responsibility once the conspiracy had formed. The mandate issued
#   January 6, 1920 and the men entered USP Atlanta on January 31, 1920.
#
#   Convicted of federal SEDITIOUS CONSPIRACY tied to resistance to the
#   Selective Service Act -- not, as the old records said, generically of
#   Espionage Act or Sedition Act offences.
#
# WHAT THIS SCRIPT DOES
#   deletes   R. Trumble (REVIEW=1 hides it instead)
#   corrects  E. A. Trumble  -> Egbert Aswell Trumble   (already in the database)
#   corrects  John Trumble   -> John William Trumble    (already in the database)
#   creates   Joe Trumble                               (was missing)
#
#   Renaming regenerates the slug, so /prisoner/e-a-trumble becomes
#   /prisoner/egbert-aswell-trumble. Pass KEEP_SLUG=1 to hold the old URLs. The
#   former names are kept as AKAs either way, so search still finds them.
#
#   Arrest dates are recorded at MONTH precision (August 1917). The raids began
#   on the night of August 8, but no source puts these three men on that
#   specific night rather than later in the sweep.
#
# EIGHT MORE RECORDS LOOK WRONG THE SAME WAY -- listed at the end, not touched.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-trumble-family.sh
#   KEEP_SLUG=1 bash database/data/fix-trumble-family.sh
#   REVIEW=1 bash database/data/fix-trumble-family.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

REVIEW="${REVIEW:-0}" KEEP_SLUG="${KEEP_SLUG:-0}" php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;

$review = getenv("REVIEW") === "1";
$keepSlug = getenv("KEEP_SLUG") === "1";

$atlanta = Institution::firstOrCreate(
    ["name" => "USP Atlanta"],
    ["city" => "Atlanta", "state" => "Georgia"],
);

$find = function (array $slugs, array $names) {
    $holes = implode(", ", array_fill(0, count($names), "?"));

    return Prisoner::withoutGlobalScopes()
        ->where(fn ($q) => $q->whereIn("slug", $slugs)
            ->orWhereRaw("LOWER(name) IN ({$holes})", $names))
        ->with("cases")
        ->get();
};

// ---- 1. remove the amalgamation -------------------------------------------
$bad = Prisoner::withoutGlobalScopes()->where("slug", "r-trumble")->with("cases")->get();
if ($bad->count() > 1) {
    echo "ABORT: more than one r-trumble record.\n";
    exit(1);
}
if ($bad->isEmpty()) {
    echo "R. Trumble: not found (already removed?)\n";
} else {
    $b = $bad->first();
    echo "R. Trumble [{$b->slug}] cases=".$b->cases->count()."\n";
    foreach ($b->cases as $c) {
        echo "   inc=".($c->incarceration_date ? $c->incarceration_date->toDateString() : "-")
            ." rel=".($c->release_date ? $c->release_date->toDateString() : "-")
            ." days=".($c->imprisoned_for_days ?? "null")."\n";
    }
    if ($review) {
        $b->under_review = true;
        $b->save();
        echo "   hidden from the site (under_review = true)\n";
    } else {
        $b->delete();
        echo "   deleted, with its case\n";
    }
}

// ---- the three men ---------------------------------------------------------
// [match slugs, match names, name, first, middle, last, aka, birth, death,
//  inmate no, age at Atlanta, ideologies, affiliation, release y/m/d,
//  sentence line, bio]
$people = [
    [
        ["e-a-trumble", "egbert-aswell-trumble"], ["e. a. trumble", "egbert aswell trumble"],
        "Egbert Aswell Trumble", "Egbert", "Aswell", "Trumble", "E. A. Trumble",
        [1858, 7, 16], [1936, 6, 18], "10602", 62,
        ["Anti-Militarism", "Draft Resistance", "Socialism"],
        ["Farmers and Laborers Protective Association", "Socialist Party of America"],
        [1920, 12, 4],
        "Two years for seditious conspiracy. Sentenced on or about September 20, 1918 but at liberty throughout the appeal; entered USP Atlanta on January 31, 1920 as prisoner 10602, age recorded as 62, and paroled on December 4, 1920 — 308 days, about ten months and three days of the two years.",
        "Egbert Aswell Trumble was a Rains County, Texas farmer, an official of the Farmers and Laborers Protective Association, a Socialist Party election official and the Socialist candidate for Rains County sheriff in 1904. He spoke at the first of the anti-draft meetings held in the county in the summer of 1917, called after local farmers complained that their draft quota was unfairly high; at a meeting on July 29 participants allegedly voted that draft-age men should resist induction and that older residents should help them. Rumours that the farmers meant to burn the town of Emory, form armed camps and attack officials proved baseless — no town was attacked, no encampment or arms cache was found, and the defendants did not resist arrest — but about fifty people were seized in raids beginning on the night of August 8, 1917. He was among the forty-six released on individual \$2,000 bonds. Tried at Tyler from September 9, 1918 in a trial lasting some ten days, he was one of twenty-three convicted, of federal seditious conspiracy connected to resistance to the Selective Service Act, and sentenced to two years alongside his son John and John O’Rear. The Fifth Circuit affirmed on November 25, 1919, holding that abandoning the planned resistance did not erase criminal responsibility once the conspiracy had formed. He entered USP Atlanta on January 31, 1920 and was paroled on December 4, 1920. His brother Joe and his son John were imprisoned with him.",
    ],
    [
        ["john-trumble", "john-william-trumble"], ["john trumble", "john william trumble"],
        "John William Trumble", "John", "William", "Trumble", "John Trumble",
        [1888, 9, 24], [1952, 5, 7], "10603", 31,
        ["Anti-Militarism", "Draft Resistance", "Socialism"],
        ["Farmers and Laborers Protective Association"],
        [1920, 12, 4],
        "Two years for seditious conspiracy. Sentenced on or about September 20, 1918 but at liberty throughout the appeal; entered USP Atlanta on January 31, 1920 as prisoner 10603, age recorded as 31, and paroled on December 4, 1920 — 308 days, about ten months and three days of the two years.",
        "John William Trumble was born on September 24, 1888 at Point in Rains County, Texas, the son of E. A. Trumble, and was a member of the Farmers and Laborers Protective Association identified in contemporary political-prisoner material as a Socialist. At the larger of the two anti-draft meetings held in the county in the summer of 1917 — called after local farmers complained their draft quota was unfairly high — he reportedly led a smaller group away from the main assembly to continue discussing resistance to induction. Rumours that the farmers meant to burn the town of Emory, form armed camps and attack officials proved baseless, but about fifty people were seized in raids beginning on the night of August 8, 1917; he was among the forty-six released on individual \$2,000 bonds. Tried at Tyler from September 9, 1918, he was one of twenty-three convicted, of federal seditious conspiracy connected to resistance to the Selective Service Act, and was sentenced to two years alongside his father and John O’Rear. The Fifth Circuit affirmed the convictions on November 25, 1919. He entered USP Atlanta on January 31, 1920 and was paroled on December 4, 1920. He died on May 7, 1952 in Lampasas County, Texas.",
    ],
    [
        ["joe-trumble"], ["joe trumble", "joe trunble"],
        "Joe Trumble", "Joe", null, "Trumble", "Joe Trunble",
        null, null, "10613", 41,
        ["Anti-Militarism", "Draft Resistance"],
        [],
        [1920, 10, 20],
        "One year and one day for seditious conspiracy. Sentenced on or about September 20, 1918 but at liberty throughout the appeal; entered USP Atlanta on January 31, 1920 as prisoner 10613, age recorded as 41. His sentence was commuted on October 13, 1920 and he was released on October 20, 1920 — 263 days. He received a full presidential pardon on June 19, 1923.",
        "Joe Trumble was a Rains County, Texas farmer, brother of E. A. Trumble and uncle of John William Trumble, convicted with them at Tyler in September 1918 of federal seditious conspiracy connected to resistance to the Selective Service Act. The case grew out of anti-draft meetings held in the county in the summer of 1917 after local farmers complained their draft quota was unfairly high; rumours of a planned uprising proved baseless, but about fifty people were seized in raids beginning on the night of August 8, 1917. He received the lightest of the family sentences, one year and one day, remained at liberty through the appeal, and entered USP Atlanta on January 31, 1920 as prisoner 10613. His sentence was commuted on October 13, 1920 and he was released a week later, on October 20. He was granted a full presidential pardon on June 19, 1923. The National Archives prisoner index appears to misspell his surname as Trunble.",
    ],
];

foreach ($people as [$slugs, $names, $name, $first, $middle, $last, $aka,
    $birth, $death, $inmate, $ageAtAtlanta, $ideologies, $affiliation,
    $release, $sentenceLine, $bio]) {

    $found = $find($slugs, $names);
    if ($found->count() > 1) {
        echo "\nABORT: {$found->count()} records match {$name}:\n";
        foreach ($found as $m) { echo "  {$m->slug}  {$m->name}\n"; }
        exit(1);
    }

    $p = $found->first();
    $created = false;
    if (! $p) {
        $p = new Prisoner(["name" => $name]);
        $created = true;
    }
    $oldSlug = $p->slug;
    $oldName = $p->name;

    $p->name = $name;
    $p->first_name = $first;
    $p->middle_name = $middle;
    $p->last_name = $last;
    $p->aka = $aka;
    $p->gender = "Male";
    $p->state = "Texas";
    $p->era = "1910s";
    $p->ideologies = $ideologies;
    $p->affiliation = $affiliation ?: null;
    $p->inmate_number = $inmate;
    $p->description = $bio;
    $p->in_custody = false;
    $p->awaiting_trial = false;
    $p->released = true;
    if ($birth) { $p->setPartialDate("birthdate", ...$birth); }
    if ($death) { $p->setPartialDate("death_date", ...$death); }
    $p->save();

    if ($keepSlug && $oldSlug && $p->slug !== $oldSlug) {
        $p->slug = $oldSlug;
        $p->save();
    }

    $c = $p->cases()->first() ?? $p->cases()->create([]);
    $c->institution_id = $atlanta->id;
    $c->charges = "Seditious conspiracy (federal) — conspiring to resist the Selective Service Act, arising from the anti-draft meetings held in Rains County, Texas in the summer of 1917.";
    $c->plead = "Not guilty";
    $c->convicted = "Yes — one of twenty-three convicted at the federal court in Tyler, Texas after a trial that opened September 9, 1918; affirmed by the Fifth Circuit on November 25, 1919, mandate issued January 6, 1920.";
    $c->sentence = $sentenceLine;
    $c->setPartialDate("arrest_date", 1917, 8);          // month precision: the raids began Aug 8, the individual day is not recorded
    $c->setPartialDate("sentenced_date", 1918, 9, 20);
    $c->setPartialDate("incarceration_date", 1920, 1, 31);
    $c->setPartialDate("release_date", ...$release);
    $c->save();

    $verb = $created ? "created" : "updated";
    echo "\n{$verb}  {$p->name}  [{$p->slug}]";
    if (! $created && $oldName !== $p->name) { echo "   (was {$oldName} [{$oldSlug}])"; }
    echo "\n";
    echo "   prisoner {$inmate}, age {$ageAtAtlanta} at Atlanta"
        ."   inc=".$c->incarceration_date->toDateString()
        ."  rel=".$c->release_date->toDateString()
        ."  days=".($c->imprisoned_for_days ?? "null")."\n";
}

// ---- audit: the rest of the Tyler cohort -----------------------------------
echo "\n---- not touched: other records with an incarceration date of 1918-09-20 ----\n";
echo "That date is when this case was convicted and sentenced, not when anyone\n";
echo "reached Atlanta -- the defendants were free on appeal until January 31, 1920.\n";
echo "Any of these from the Tyler trial has the same fifteen-month error.\n\n";

$others = PrisonerCase::query()
    ->whereDate("incarceration_date", "1918-09-20")
    ->with(["prisoner" => fn ($q) => $q->withoutGlobalScopes()])
    ->get()
    ->filter(fn ($c) => $c->prisoner)
    ->sortBy(fn ($c) => $c->prisoner->name);

if ($others->isEmpty()) {
    echo "  none left.\n";
} else {
    foreach ($others as $c) {
        echo "  ".str_pad($c->prisoner->name, 26)
            ." rel=".str_pad($c->release_date ? $c->release_date->toDateString() : "-", 12)
            ." /prisoner/".$c->prisoner->slug."\n";
    }
    echo "\n  ".$others->count()." record(s). John O’Rear at least was a co-defendant -- the dossier\n";
    echo "  names him with the same two-year sentence. Send confirmation for the others\n";
    echo "  and they can be corrected the same way.\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone. Run: php artisan prisoners:auto-place-zero-sort to position Joe Trumble.\n";
'

echo
echo "Done."
