#!/usr/bin/env bash
#
# BATCH 189 -- "What to the New Afrikan is Juneteenth?" by Monsour
# Owolabi, published as an article.
#
#   THE AUTHOR IS ALREADY IN THIS ARCHIVE as a prisoner: Monsour
#   Owolabi, serving life without parole in Texas on a conviction he
#   contests, held in prolonged isolation his supporters attribute to
#   his organizing. He had no AUTHOR record, so one is created and the
#   article links back to his prisoner page. A man this archive
#   documents is now also a byline in it.
#
#   HIS ORTHOGRAPHY IS LEFT EXACTLY AS WRITTEN, and this is the part
#   most likely to be "corrected" later by someone meaning well. He
#   writes the first-person singular in lowercase and capitalises We, Us
#   and Our — a New Afrikan convention that subordinates the individual
#   to the collective. The stored body carries 18 lowercase standalone
#   i's, 13 capitalised We's and 8 Our's. They are not typos. The
#   opening note says so on the page itself, so a future editor is
#   warned before reaching for the spellchecker.
#
#   FILED UNDER PUBLICATIONS, which puts it at
#   /publications/what-to-the-new-afrikan-is-juneteenth — article URLs
#   take their prefix from the category slug — and alongside the other
#   long-form essays rather than in the news feed.
#
#   REPRODUCED IN FULL, from the campaign that exists to circulate his
#   writing. The source is credited in the opening line, linked twice,
#   and recorded in the citations panel. No explicit licence is attached
#   to the original. If the campaign would rather this ran as an excerpt
#   pointing at their post, shortening the body is a one-field change
#   and the credit already stands.
#
#   THE ILLUSTRATION is the 19th-century Emancipation Day group
#   photograph the original post used — public domain, and captioned as
#   such. Substack chrome (subscribe buttons, share widgets, inline
#   SVGs) was stripped; what remains is paragraphs, emphasis and the
#   author's own outbound links, which open in new tabs.
#
#   Idempotent: the article is created only when its slug is absent, and
#   the author only when the slug is absent.
#
# Run from the repo root, after git pull (after batch 188):
#   bash database/data/run-batch-189.sh

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
echo "  Batch 189 — What to the New Afrikan is Juneteenth?"
echo "==================================================================="

SRC="database/data/files/articles/what-to-the-new-afrikan-is-juneteenth.jpg"
DEST_DIR="storage/app/public/articles"

echo
echo "--- install-image"
install_ok=1
mkdir -p "$DEST_DIR"
if [ ! -f "$SRC" ]; then
    echo "  missing source file: $SRC"; install_ok=0
else
    dest="$DEST_DIR/$(basename "$SRC")"
    if [ -f "$dest" ] && cmp -s "$SRC" "$dest"; then
        echo "  $(basename "$SRC") — already installed, identical"
    else
        cp "$SRC" "$dest"
        echo "  $(basename "$SRC") — $(stat -c%s "$dest") bytes installed"
    fi
    if [ ! -e "public/storage/articles/$(basename "$SRC")" ]; then
        echo "  !! not reachable through the public symlink — run php artisan storage:link"
        install_ok=0
    fi
fi
[ "$install_ok" -eq 1 ] || FAILED+=("install-image")

PUBLISH_CODE='
use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch189.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$a = $payload["article"];
$au = $payload["author"];

$category = Category::where("title", $a["category"])->first();

if (! $category) {
    echo "  category ", $a["category"], " not found — stopping rather than creating one.\n";

    return;
}

// He is in this archive as a prisoner; this gives him a byline too.
$author = Author::where("slug", $au["slug"])->first();

if (! $author) {
    $author = Author::create(["name" => $au["name"], "about" => $au["about"]]);
    echo "  author created: ", $author->name, " [", $author->slug, "]\n";
} else {
    echo "  author already present: ", $author->name, " [", $author->slug, "]\n";
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
        "image"          => $a["image"],
        "image_caption"  => $a["image_caption"],
        "citations_json" => $a["citations"],
    ]);
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
echo "    image      ", $article->image, "  ",
    (Storage::disk("public")->exists((string) $article->image) ? "on disk" : "MISSING ON DISK"), "\n";

// The orthography is the thing most likely to be undone by a later edit, so
// it is counted here rather than assumed.
$lower = preg_match_all("/(?<![A-Za-z])i(?![A-Za-z])/u", $text);
$we = preg_match_all("/\bWe\b/u", $text);
$our = preg_match_all("/\bOur\b/u", $text);

echo "\n    orthography preserved: ", $lower, " lowercase i, ", $we, " We, ", $our, " Our\n";
echo "    ", wordwrap($payload["orthography_note"], 70, "\n    "), "\n";
echo "\n    ", wordwrap($payload["permission_note"], 70, "\n    "), "\n";

if ($lower > 0 && Storage::disk("public")->exists((string) $article->image)) { echo "B189-OK\n"; }
'

run_tinker "publish-article" "B189-OK" "$PUBLISH_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 189 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
