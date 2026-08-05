#!/usr/bin/env bash
#
# BATCH 153 -- re-paragraph "WHO IS A POLITICAL PRISONER?"
#   /publications/who-is-a-political-prisoner
#
#   The whole 37,923-character article was stored inside ONE <p> tag.
#   Paragraph breaks had been lost at import, so sentences ran
#   together with no space between them —
#
#     ...dwelled for "social disillusionment."It would be "un-American"...
#     ...institution of political prisoners.WHO IS A POLITICAL PRISONER?DURING 1969...
#
#   — and two section headings sat inline inside the prose. It is now
#   37 paragraphs and 3 headings.
#
#   NOT ONE WORD IS CHANGED. Breaks were inserted only where a
#   sentence ends and a capital begins with no space, with
#   single-letter abbreviations excluded so U.S. and U.S.A. are not
#   split apart. Openings set in capitals as a typographic lead-in —
#   THE MAJORITY of, GRANTED that, FORTUNE MAGAZINE, as long ago —
#   stay as paragraphs, because the word after the capitals is
#   lower-case; only a capitalised phrase followed by a fresh sentence
#   became a heading. The repaired body was compared with the stored
#   one with all markup and whitespace stripped: 30,889 characters
#   before, 30,889 after, identical.
#
#   THE PDF EMBED WAS ALSO ESCAPED, so the page printed the literal
#   text "<iframe src=...>" where the document should have been. It is
#   unescaped here, and that accounts for the whole difference in
#   length between the two versions.
#
#   The script prints the first line of every paragraph it writes, and
#   re-runs are safe: the body is replaced wholesale with the same
#   value.
#
# Run from the repo root, after git pull (after batch 152):
#   bash database/data/run-batch-153.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"
    shift
    echo
    echo "--- ${label}"
    if "$@"; then return 0; fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}")
    return 0
}

echo "==================================================================="
echo "  Batch 153 — re-paragraph WHO IS A POLITICAL PRISONER?"
echo "==================================================================="

fix_body() {
    php artisan tinker --execute='
use App\Models\Article;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch153.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$a = Article::where("slug", $payload["slug"])->first();

if (! $a) { echo "  ", $payload["slug"], " NOT FOUND — nothing changed.\n"; return; }

$strip = fn ($h) => preg_replace("/\s+/", "", html_entity_decode(strip_tags((string) $h)));

$before = (string) $a->body;

echo "  article: ", $a->title, "\n";
echo "  before:  ", strlen($before), " chars, ",
    substr_count($before, "<p>"), " paragraph(s), ",
    substr_count($before, "<h2"), " heading(s)\n";

$a->body = $payload["body"];
$a->save();

$after = (string) $a->refresh()->body;

echo "  after:   ", strlen($after), " chars, ",
    substr_count($after, "<p>"), " paragraph(s), ",
    substr_count($after, "<h2"), " heading(s)\n";

// The one check that matters: no word may have changed. The escaped iframe
// counts as text before and as markup after, so it is removed from both
// sides of the comparison.
$b = $strip(preg_replace("/&lt;iframe.*?&lt;\/iframe&gt;/s", "", $before));
$c = $strip(preg_replace("/<iframe.*?<\/iframe>/s", "", $after));

echo "\n  prose characters before: ", strlen($b), "\n";
echo "  prose characters after:  ", strlen($c), "\n";
echo "  ", ($b === $c ? "IDENTICAL — no word changed." : "!! DIFFERENT — investigate before publishing."), "\n";

echo "\n  headings now on the page:\n";

foreach ($payload["headings"] as $h) { echo "    ", $h, "\n"; }

echo "\n  first line of each paragraph:\n";

preg_match_all("/<p>(.*?)<\/p>/s", $after, $m);

foreach ($m[1] as $i => $p) {
    $t = trim(html_entity_decode(strip_tags($p)));
    echo "    ", str_pad((string) ($i + 1), 3, " ", STR_PAD_LEFT), ". ", mb_strimwidth($t, 0, 84, "..."), "\n";
}

echo "\n  PDF embed rendered rather than printed as text: ",
    (str_contains($after, "<iframe src=\"/storage/") ? "yes" : "NO — check it"), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "repaginate-article" fix_body

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 153 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
