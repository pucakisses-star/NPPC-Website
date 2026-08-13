#!/usr/bin/env bash
#
# BATCH 208 -- press release: the death of Paki Wieland.
#
#   SHE DIED ON MARCH 12, 2026, aged 82, at her daughter's home in
#   Conway, Massachusetts, after several years of treatment for lung
#   cancer. Born September 15, 1943 in New Orleans. Confirmed against
#   four sources before a word was written -- the Gazette, Massachusetts
#   Peace Action, Representative McGovern's office and the published
#   obituary -- because a press release announcing a death is the one
#   piece of writing that must never be wrong about whether the death
#   happened.
#
#   ONE SOURCE DISAGREED and was checked rather than averaged. Traprock
#   renders the death as early Thursday, March 14; March 14, 2026 was a
#   Saturday, and March 12 was the Thursday. The obituary, MAPA and the
#   Gazette all give the 12th. March 12 it is.
#
#   SHE IS NOT IN THE PRISONER DATABASE, and the release says so in its
#   second paragraph rather than implying otherwise. Wieland was arrested
#   repeatedly and released repeatedly; she did not serve the kind of
#   sentence this archive documents. Writing around that would have been
#   the easy thing and the dishonest one, so the distinction becomes the
#   subject of a paragraph instead.
#
#   TWO KINDS OF QUOTATION, and they are not equivalent. The McGovern
#   passages are verbatim from his official statement of March 13, 2026 --
#   a United States government work -- and are attributed to him. The
#   spokesperson quote is written for the coalition, which is what a
#   press release is, and follows the house convention already used in
#   the store and quiz releases. Nobody real is quoted saying words they
#   did not say.
#
#   DATED MARCH 13, NOT TODAY. An obituary filed five months late under
#   an August date reads as though the coalition had only just noticed.
#   The news feed is chronological by subject, so this places it where a
#   reader looking for March 2026 will find it. One field to change if
#   the archive would rather it carry its true publication date.
#
#   NO PHOTOGRAPH, and that costs something. Commons has nothing of her,
#   and every picture in the coverage is a working news photograph
#   belonging to the Gazette, the Recorder or Mass Peace Action. Batches
#   193 and 194 had just closed the last gap of articles without images;
#   this reopens it by exactly one. Her family or MAPA could give
#   permission for a portrait, which would settle it properly.
#
#   WHAT IS DELIBERATELY ABSENT: any number of arrests, any specific
#   charge, conviction or jail term, the years of individual actions, and
#   any claim about travel to Gaza or Iraq. The coverage says repeatedly
#   that she was arrested many times without ever enumerating them, so
#   the release says she was arrested more times than most people could
#   name -- which is what the sources support.
#
#   Idempotent: the article is created only when its slug is absent, and
#   the slug is passed explicitly rather than derived, so a second run
#   cannot produce a near-duplicate.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-208.sh

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
echo "  Batch 208 — press release: Paki Wieland, 1943-2026"
echo "==================================================================="

PUBLISH_CODE='
use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch208.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$a = $payload["article"];

$category = Category::where("title", $a["category"])->first();

if (! $category) {
    echo "  category ", $a["category"], " not found — stopping rather than creating one.\n";

    return;
}

$author = Author::where("slug", $a["author_slug"])->first();

if (! $author) {
    echo "  author ", $a["author_slug"], " not found — stopping. Batch 186 merges the old\n";
    echo "  NPPC record into this one; run it first if it has not run here.\n";

    return;
}

$article = Article::where("slug", $a["slug"])->first();

if ($article) {
    echo "  article already exists — not created again.\n";
} else {
    // The slug is passed rather than left to HasSlug: the check above keys on
    // it, and a derived slug would never match, so a re-run would duplicate.
    $article = Article::create([
        "slug"           => $a["slug"],
        "title"          => $a["title"],
        "intro"          => $a["intro"],
        "body"           => $a["body"],
        "category_id"    => $category->id,
        "author_id"      => $author->id,
        "published_at"   => $a["published_at"],
        "citations_json" => $a["citations"],
    ]);

    echo "  created.\n";
}

$article->refresh();

$text = html_entity_decode(strip_tags((string) $article->body));

echo "\n  ", $article->title, "\n";
echo "    url        ", $article->url, "\n";
echo "    byline     ", $article->author?->name, "\n";
echo "    category   ", $article->category?->title, "\n";
echo "    published  ", $article->published_at?->toDateString(), "\n";
echo "    words      ", str_word_count($text), "\n";
echo "    citations  ", count($article->citations ?? []), "\n";
echo "    image      ", ($article->image ?: "(none — see the note below)"), "\n";

// The two claims the release rests on, checked in the stored text rather than
// trusted: the death date, and the statement that she is not in the database.
$hasDate = str_contains($text, "March 12, 2026");
$saysNotInDb = str_contains($text, "she is not in our database");

echo "\n    death date present in the text:       ", ($hasDate ? "yes" : "NO"), "\n";
echo "    says plainly she is not a prisoner:   ", ($saysNotInDb ? "yes" : "NO"), "\n";

echo "\n  ", wordwrap($payload["not_a_prisoner_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["quote_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["no_image_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["unverified_note"], 72, "\n  "), "\n";

$ok = $hasDate
    && $saysNotInDb
    && $article->category?->title === $payload["expected"]["category"]
    && $article->author?->name === $payload["expected"]["author"]
    && $article->published_at?->toDateString() === $payload["expected"]["published_at"];

if ($ok) { echo "\nB208-OK\n"; }
'

run_tinker "publish-release" "B208-OK" "$PUBLISH_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 208 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
