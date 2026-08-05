#!/usr/bin/env bash
#
# BATCH 154 -- embed the PDF properly, and stop the title shouting.
#   /publications/who-is-a-political-prisoner
#
#   THE PDF WAS NOT ON THE SERVER. The body pointed an iframe at
#   /storage/qt23p521p4_noSplash_b55493b510249c77305d1e9a270c4c04.pdf
#   and that path returns 404 on the live site — so batch 153
#   unescaping the iframe would have produced an empty viewer rather
#   than a document. The file is Ron Ridenour pamphlet, held by
#   eScholarship at the University of California.
#
#   A copy is committed to database/data/files and installed into
#   storage/app/public here, rather than downloaded at run time.
#   eScholarship refuses plain requests: it returns the PDF only to a
#   browser user-agent carrying a referer, which a deploy script would
#   not survive. Shipping the bytes makes this deterministic.
#
#   The bare iframe becomes a figure that sizes to the viewport, has a
#   title for screen readers, links the PDF directly in case the
#   inline viewer fails, and credits eScholarship.
#
#   THE TITLE was stored in capitals. The h1 applies no
#   text-transform, so the shouting was in the data, not the styling.
#   It becomes "Who Is a Political Prisoner?". The heading of the same
#   words inside the body stays in capitals: that is the pamphlet own
#   typography rather than a display choice of this site.
#
#   Idempotent. Any existing embed, escaped or not, is removed before
#   the new one is appended, so this is safe whether or not batch 153
#   has run.
#
# Run from the repo root, after git pull (after batch 153):
#   bash database/data/run-batch-154.sh

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
echo "  Batch 154 — embed the PDF, and title-case the title"
echo "==================================================================="

PDF="qt23p521p4_noSplash_b55493b510249c77305d1e9a270c4c04.pdf"
SRC="database/data/files/${PDF}"
DEST="storage/app/public/${PDF}"

install_pdf() {
    if [ ! -f "$SRC" ]; then
        echo "  source missing: $SRC"
        return 1
    fi

    mkdir -p storage/app/public

    if [ -f "$DEST" ] && cmp -s "$SRC" "$DEST"; then
        echo "  already installed, identical: $DEST"
    else
        cp "$SRC" "$DEST"
        echo "  installed: $DEST"
    fi

    ls -l "$DEST"

    # It must actually be a PDF, and it must be reachable through the
    # public symlink the page will use.
    head -c 5 "$DEST" | grep -q '%PDF' \
        && echo "  header check: looks like a PDF" \
        || { echo "  !! header check FAILED — not a PDF"; return 1; }

    if [ -e "public/storage/${PDF}" ]; then
        echo "  reachable at public/storage/${PDF}"
    else
        echo "  !! NOT reachable at public/storage/${PDF}"
        echo "     run: php artisan storage:link"
        return 1
    fi
}

update_article() {
    php artisan tinker --execute='
use App\Models\Article;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch154.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$a = Article::where("slug", $payload["slug"])->first();

if (! $a) { echo "  ", $payload["slug"], " NOT FOUND — nothing changed.\n"; return; }

echo "  title before: ", $a->title, "\n";

$a->title = $payload["title"];

$body = (string) $a->body;

// Remove any embed already present, in either state, so a re-run and a
// run after batch 153 both land in the same place.
$body = preg_replace("/<figure class=\"pdf-embed\".*?<\/figure>/s", "", $body);
$body = preg_replace("/<iframe[^>]*qt23p521p4[^>]*>.*?<\/iframe>/s", "", $body);
$body = preg_replace("/&lt;iframe[^&]*qt23p521p4.*?&lt;\/iframe&gt;/s", "", $body);
$body = preg_replace("/<p>\s*<\/p>/", "", $body);

$a->body = rtrim($body)."\n\n".$payload["embed"];
$a->save();
$a->refresh();

echo "  title after:  ", $a->title, "\n";
echo "  slug:         ", $a->slug, " (unchanged — the URL still works)\n";
echo "\n  embeds in the body now: ",
    substr_count((string) $a->body, "<figure class=\"pdf-embed\""), " (want 1)\n";
echo "  stray escaped iframes:  ",
    substr_count((string) $a->body, "&lt;iframe"), " (want 0)\n";
echo "  paragraphs preserved:   ", substr_count((string) $a->body, "<p>"), "\n";
echo "  headings preserved:     ", substr_count((string) $a->body, "<h2"), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "install-pdf" install_pdf
run "update-article" update_article

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 154 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "Check the page: the PDF should render inline, and the heading should"
echo "read 'Who Is a Political Prisoner?' rather than shouting."
