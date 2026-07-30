#!/usr/bin/env bash
#
# BIO TYPO AUDIT, ROUND 1 -- 82 corrections across 70 records.
#
# HOW THESE WERE FOUND, AND WHY THE FIRST METHOD WAS THROWN AWAY.
# Hand-rolled regex detectors over 8,321 descriptions produced about
# 1,300 flags and almost no real errors:
#
#   ends without terminal punctuation   811 flags   ~0 real (URLs, and
#                                                   the WWI objector
#                                                   imports that end in
#                                                   a deliberate "; …")
#   double punctuation                  153 flags    0 real (D.C., Jr.)
#   doubled word                         97 flags    0 real (Walla
#                                                   Walla is a prison,
#                                                   Sha Sha a nickname,
#                                                   "had had" correct)
#   hand-rolled misspelling list         26 flags    2 real (the rest
#                                                   were Greenwich,
#                                                   Norwich, sandwich,
#                                                   Ipswich...)
#
# One genuinely reassuring result: the five hits for the retired
# age-strip damage pattern were all legitimate prose, so nothing of
# that command survives in the corpus.
#
# What worked was a real 160,000-word dictionary over every bio, with
# URLs masked out, then THREE targeted passes: a function word glued to
# the next word, a word broken apart by a space, and a rare unknown
# token whose nearest dictionary neighbour is a common word. Every
# surviving flag was then read in context by hand. Roughly three
# quarters were still false positives and are deliberately NOT changed:
#
#   - British and variant spellings: favour, organised, recognised,
#     counselling, honourably, mouldy, parlour, rumours, naturalised,
#     criminalisation, publicising. Some sit inside quotations from
#     British, Irish and Australian sources. Not errors.
#   - accented or foreign words the dictionary lacks: attaché, fiancée,
#     communiqué, emigre, en masse, nolo contendere, nolle prossed,
#     cum laude, avant garde, and Spanish and Italian book titles
#     (emancipazione, liberazione, historia, lucha, campesinos).
#   - modern vocabulary: doxxed, fracking, fragging, hashtag, bitcoin,
#     darknet, cypherpunk, ecotage, emoji, hoodie, flashbang, rideshare,
#     takedown, tazed, netizen, podcasts, safehouse, vivisectors.
#   - proper nouns whose accents were stripped by the tokeniser:
#     Álvarez, Cárdenas, Hägglund (Joe Hill), Bečkerek, Tułowice,
#     Köflach, Praxedis, Wet{39}suwet{39}en, Krest{39}ianin.
#   - "offence"/"offences", which a US dictionary rejects and which are
#     correct in the Irish republican extradition records that use them.
#
# ALSO LEFT ALONE ON PURPOSE: the psychologist{39}s quoted verdict on
# Glen H. Witherbee, "basest type of malingerer; a brazon hypocrite".
# That is quoted archival material and the misspelling may be faithful
# to the original document, so it stands. Same reasoning spared the
# dialect in Shields Green{39}s "I b{39}lieve I{39}ll go with the old man" and
# the LulzSec handle "wildicv".
#
# WHAT IS ACTUALLY BEING FIXED falls into four groups:
#
#   1. Hyphenation artifacts from OCR of printed sources, where a line
#      break became a space: "lead- ership", "con- spiracy",
#      "sen- tenced", "con- victed", "dis turbing", "al leged",
#      "sus pended".
#   2. Lost spaces, the same OCR damage in reverse: andsentenced,
#      andcoughing, andhinder, andthree, anddetention, andhe, whenhe,
#      onbond, infront, abunch, acarpenter, andamember, himon, and the
#      badly mangled Pete Muselin sentence
#      ("Tom Zima{39}shouseand Muselin{39}sbarber shop, wherea large ...
#      Thethree defendants").
#   3. Plain misspellings: authorities, cruelty, declared, destroyed,
#      during, former, found, indispensable, inherently, inoculation,
#      interviewed, joined, later, listed, machinery, military,
#      militant, movement, receive, registered, sentenced (three
#      different manglings), shanghaied, solitary, transferred (twice),
#      territories, timing, wife, workingmen, worry, innuendo,
#      confinement, disproportionate, transportation, property,
#      enlistment, misspelled, hacktivization -> hacktivist, began,
#      individual, "remaine din" -> "remained in", and the OCR "vears"
#      and "yeursin" for "years in" in three Senate-hearing records.
#   4. Two invisible-character bugs the dictionary pass exposed:
#      soft hyphens (U+00AD) inside "$34 r<AD><AD>obbery" on the Richard
#      Lake record, and a mojibake u-umlaut standing in for an opening
#      quote in "states: uenassigned," on Edward Salzer.
#
# EVERY REPLACEMENT WAS VERIFIED AGAINST THE LIVE DATA BEFORE SHIPPING:
# 79 of the 80 search strings occur exactly once in the record they
# target; the Minnie Abbott one occurs twice, both the same error, and
# str_replace fixes both.
#
# The table lives in database/data/fixes/bio-typos-1.json because
# several strings contain apostrophes, which cannot appear inside the
# single-quoted tinker block.
#
# Idempotent: a replacement whose search string is gone is skipped and
# reported, so a second run reports nothing to do.
#
# Run from the repo root:
#   bash database/data/fix-bio-typos-1.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$path = database_path("data/fixes/bio-typos-1.json");
$rows = json_decode(File::get($path), true);
if (! is_array($rows) || ! count($rows)) {
    echo "PAYLOAD MISSING OR EMPTY\n";
    exit(1);
}

$applied = 0;
$already = 0;
$missing = 0;
$touched = [];

foreach ($rows as $row) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->first();
    if (! $p) {
        echo "  NOT FOUND: {$row["slug"]}\n";
        $missing++;
        continue;
    }

    $desc = (string) $p->description;
    if (! str_contains($desc, $row["find"])) {
        $already++;
        continue;
    }

    $n = substr_count($desc, $row["find"]);
    $p->description = str_replace($row["find"], $row["replace"], $desc);
    $p->save();
    $applied++;
    $touched[$row["slug"]] = true;
    echo "  ", str_pad($row["slug"], 26), " ", $row["find"], "  ->  ", $row["replace"],
         ($n > 1 ? "   (".$n." occurrences)" : ""), "\n";
}

echo "\nReplacements applied: {$applied}\n";
echo "Records touched:      ", count($touched), "\n";
echo "Already correct (search string absent): {$already}\n";
echo "Slugs not found:      {$missing}\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
