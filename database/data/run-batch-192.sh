#!/usr/bin/env bash
#
# BATCH 192 -- spacing and capitalization on "What to the New Afrikan is
# Juneteenth?", plus the campaign's name.
#
#   NOTHING HERE WAS BROKEN BY THE IMPORT. Every defect corrected below
#   was checked against the source post on Substack first, and all of it
#   is in the original: the six stray capital Is, the em dashes closed on
#   one side and open on the other, the hyphen standing in for a dash,
#   the literal double hyphen. Batch 189 reproduced the essay faithfully.
#   So this is a deliberate light copy-edit of the reproduction, not a
#   repair of a broken one, and the note at the top of the article is
#   rewritten to say so instead of continuing to claim the text is left
#   exactly as written.
#
#   HIS ORTHOGRAPHY IS NOT TOUCHED, and this is the line that matters.
#   The lowercase first-person singular and the capitalized We, Us and
#   Our are a New Afrikan usage that subordinates the individual to the
#   collective; they are the point of the piece, not typos. Capitalization
#   moves in ONE direction here: six standalone capital Is are lowered to
#   i. That strengthens the convention rather than breaking it, and it is
#   almost certainly restoring what he wrote -- a standalone i is exactly
#   what a phone keyboard or a word processor auto-capitalizes. Not one
#   lowercase i is raised.
#
#   THE LOWERCASE we, us AND our ARE LEFT ALONE. The essay mixes them
#   with the capitalized forms and the mix looks like it carries meaning:
#   "in socialist and communist parlance we would refer to" and "again in
#   our parlance" are a different we from "We as New Afrikan / Black
#   people". Deciding which of the rest he meant as the collective would
#   be interpreting his politics for him. If the campaign wants them
#   normalized, that is their call and a separate pass.
#
#   THE DASH STYLE FOLLOWS HIS OWN MAJORITY. The essay uses a spaced em
#   dash throughout -- "security guards" — for lack of a better term",
#   "enslaved — and they still are". The handful that were closed on one
#   side are brought into line with the many that were not. No house
#   style is imposed.
#
#   THE CAMPAIGN IS NOT CALLED "MONSOUR OWOLAB FREEDOM CAMPAIGN". Batch
#   189 de-slugged the Substack URL and produced Owolab, dropping the i
#   from his surname, then printed it four times. Its own name -- from
#   the meta author tag, the JSON-LD author record, author_name,
#   primary_profile_name, contributors, publishedBylines, and its
#   self-description as "his defense committee, the Monsour Freedom
#   Campaign" -- is MONSOUR FREEDOM CAMPAIGN, with no surname at all.
#   Fixed twice in the body and in both citation entries. The URLs keep
#   the original spelling, because that genuinely is the address.
#
#   EVERY REPLACEMENT IS EXACT AND ACCOUNTED FOR. Each is expected to
#   match once; a replacement that matches neither its old form nor its
#   new one fails the batch rather than passing quietly. The counts of
#   standalone i, We, Us and Our are checked against the payload
#   afterwards, so a run that silently mangled the orthography could not
#   report success.
#
#   Idempotent: a second run finds every replacement already applied.
#
# Run from the repo root, after git pull (after batch 189):
#   bash database/data/run-batch-192.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

# tinker exits 0 even when the code inside throws; success is a sentinel
# the step prints as its last act.
run_tinker() {
    local label="$1" sentinel="$2" code="$3" out
    echo; echo "--- ${label}"
    out=$(php artisan tinker --execute="$code" 2>&1) || true
    printf '%s\n' "$out"
    if ! grep -q "$sentinel" <<<"$out"; then
        echo "  !! FAILED: ${label} — sentinel ${sentinel} missing (exception above?)"
        FAILED+=("${label}")
    fi
}

echo "==================================================================="
echo "  Batch 192 — Juneteenth essay: spacing, capitalization, campaign name"
echo "==================================================================="

EDIT_CODE='
use App\Models\Article;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch192.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$article = Article::where("slug", $payload["article"]["slug"])->first();

if (! $article) {
    echo "  no article at slug ", $payload["article"]["slug"], " — run batch 189 first. Nothing changed.\n";

    return;
}

$plain = function (string $h): string {
    return html_entity_decode(preg_replace("/<[^>]+>/u", "", $h));
};

$stats = function (string $h) use ($plain): array {
    $t = $plain($h);

    return [
        "standalone_I"     => preg_match_all("/(?<![A-Za-z\x{2019}])I(?![A-Za-z\x{2019}])/u", $t),
        "standalone_i"     => preg_match_all("/(?<![A-Za-z\x{2019}])i(?![A-Za-z\x{2019}])/u", $t),
        "We"               => preg_match_all("/\bWe\b/u", $t),
        "Us"               => preg_match_all("/\bUs\b/u", $t),
        "Our"              => preg_match_all("/\bOur\b/u", $t),
        "irregular_dashes" => preg_match_all("/(?<! )[\x{2013}\x{2014}]|[\x{2013}\x{2014}](?! )/u", $t),
        "double_hyphen"    => mb_substr_count($t, " -- "),
    ];
};

$body = (string) $article->body;
$before = $stats($body);

$applied = 0; $alreadyDone = 0; $missing = [];

foreach ($payload["replacements"] as $r) {
    $hits = mb_substr_count($body, $r["from"]);

    if ($hits > 0) {
        $body = str_replace($r["from"], $r["to"], $body);
        $applied++;
        echo "  fixed  (", $hits, ")  ", $r["why"], "\n";
        echo "           ", mb_strimwidth($r["from"], 0, 92, "..."), "\n";
        echo "        -> ", mb_strimwidth($r["to"], 0, 92, "..."), "\n";

        continue;
    }

    if (mb_substr_count($body, $r["to"]) > 0) {
        $alreadyDone++;

        continue;
    }

    $missing[] = $r["why"];
    echo "  !! NOT FOUND, neither old nor new form: ", mb_strimwidth($r["from"], 0, 80, "..."), "\n";
}

// The campaign name, in the body and in both citation entries.
$c = $payload["campaign_name"];
$nameHits = mb_substr_count($body, $c["from"]);
$body = str_replace($c["from"], $c["to"], $body);

$citations = $article->citations_json ?: [];
$citeHits = 0;

foreach ($citations as $k => $entry) {
    if (isset($entry["content"]) && mb_substr_count((string) $entry["content"], $c["from"]) > 0) {
        $citeHits += mb_substr_count((string) $entry["content"], $c["from"]);
        $citations[$k]["content"] = str_replace($c["from"], $c["to"], (string) $entry["content"]);
    }
}

$trailing = 0;

if (! empty($payload["strip_trailing_space_before_p"])) {
    $trailing = mb_substr_count($body, " </p>");
    $body = str_replace(" </p>", "</p>", $body);
}

$bodyChanged = $body !== (string) $article->body;
$citesChanged = $citations !== ($article->citations_json ?: []);

if ($bodyChanged) { $article->body = $body; }
if ($citesChanged) { $article->citations_json = $citations; }
if ($bodyChanged || $citesChanged) { $article->save(); $article->refresh(); }

$after = $stats((string) $article->body);

echo "\n  replacements applied: ", $applied, "   already applied: ", $alreadyDone,
    "   NOT FOUND: ", count($missing), "\n";
echo "  campaign name corrected: ", $nameHits, " in the body, ", $citeHits, " in citations\n";
echo "  trailing spaces stripped before </p>: ", $trailing, "\n";
echo "  body ", ($bodyChanged ? "saved" : "unchanged"), ", citations ", ($citesChanged ? "saved" : "unchanged"), "\n";

echo "\n  what moved                     before   after   expected\n";

$expected = $payload["expected_after"];
$mismatch = [];

foreach ($expected as $key => $want) {
    $got = $after[$key];
    $flag = $got === $want ? "" : "   !! MISMATCH";

    if ($got !== $want) { $mismatch[] = $key; }

    echo "    ", str_pad($key, 29), str_pad((string) $before[$key], 9), str_pad((string) $got, 8),
        str_pad((string) $want, 9), $flag, "\n";
}

echo "\n  We, Us and Our each rise by one because the rewritten note at the top of\n";
echo "  the article names them. No occurrence in his prose was touched.\n";

echo "\n  ", wordwrap($payload["provenance_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["orthography_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["we_us_our_note"], 72, "\n  "), "\n";

if (! $missing && ! $mismatch) { echo "\nB192-OK\n"; }
'

run_tinker "fix-spacing-and-caps" "B192-OK" "$EDIT_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 192 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
