#!/usr/bin/env bash
#
# BATCH 190 -- a photograph for Monsour Owolabi's author page.
#
#   THE AUTHOR RECORD ALREADY EXISTS. Batch 189 created it when his essay
#   "What to the New Afrikan is Juneteenth?" was published, with a name
#   and a biography but no picture, so /author/monsour-owolabi renders a
#   bare initial placeholder and his byline shows an empty circle. This
#   sets the avatar and nothing else -- the about text and his one
#   article are left exactly as batch 189 left them.
#
#   THE PHOTOGRAPH is the one the curator supplied, from the same
#   campaign Substack the essay came from. He is inside, in prison
#   whites at a cell door, wearing a beaded necklace with a red and green
#   pendant, his right fist raised.
#
#   THE FIST DOES NOT SURVIVE THE CROP, and that is a real loss worth
#   naming. Author avatars render at 32px and 64px circles on bylines, an
#   84px rounded square in the homepage featured strip, and a 176px
#   circle on the author page -- all object-fit: cover. At 32px anything
#   wider than head-and-shoulders stops reading as a face, so the frame
#   is cropped to 540x540 around the head and the raised fist falls
#   outside it. The uncropped original is committed alongside at
#   database/data/files/authors/monsour-owolabi-uncropped.jpg, so the
#   full frame stays with the archive even if the Substack URL goes away.
#   Nothing in the site reads that file; it is provenance.
#
#   THIS PUTS HIM IN THE FEATURED-AUTHORS ROTATION. The homepage strip
#   takes every author with an avatar, a published article, and a name
#   that is not an organisation placeholder, and shows four at random per
#   load. He already had the article and the name; the missing avatar was
#   the only thing holding him out. The pool goes from ten to eleven, and
#   a man this archive documents as a prisoner starts appearing on the
#   homepage as a writer. His essay is already under Publications from
#   batch 189, so batch 188's invariant still holds and 188 does not need
#   re-running.
#
#   IF THE AUTHOR IS ABSENT this batch stops rather than creating one.
#   An author with no article is half a record; the fix in that case is
#   to run batch 189 first.
#
#   Idempotent: the file is copied only when it differs, and the avatar
#   is set only when it differs.
#
# Run from the repo root, after git pull (after batch 189):
#   bash database/data/run-batch-190.sh

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
echo "  Batch 190 — Monsour Owolabi, author photograph"
echo "==================================================================="

SRC="database/data/files/authors/monsour-owolabi.jpg"
DEST_DIR="storage/app/public/authors"

echo
echo "--- install-photo"
install_ok=1
mkdir -p "$DEST_DIR"
if [ ! -f "$SRC" ]; then
    echo "  missing source file: $SRC"; install_ok=0
else
    dest="$DEST_DIR/monsour-owolabi.jpg"
    if [ -f "$dest" ] && cmp -s "$SRC" "$dest"; then
        echo "  monsour-owolabi.jpg — already installed, identical"
    else
        [ -f "$dest" ] && echo "  monsour-owolabi.jpg — $(stat -c%s "$dest") bytes -> $(stat -c%s "$SRC")" \
                       || echo "  monsour-owolabi.jpg — new file"
        cp "$SRC" "$dest"
    fi
    if [ ! -e "public/storage/authors/monsour-owolabi.jpg" ]; then
        echo "  !! not reachable through the public symlink — run php artisan storage:link"
        install_ok=0
    else
        echo "  $(stat -c%s "$dest") bytes in place"
    fi
fi
[ "$install_ok" -eq 1 ] || FAILED+=("install-photo")

UPDATE_CODE='
use App\Models\Author;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch190.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$a = $payload["author"];

$author = Author::where("slug", $a["slug"])->first();

// Deliberately not created here: an author with no article is half a record,
// and batch 189 is the batch that makes the whole one.
if (! $author) {
    echo "  no author at slug ", $a["slug"], " — run batch 189 first. Nothing changed.\n";

    return;
}

$was = $author->avatar ?: "(none)";

if ($author->avatar !== $a["avatar"]) {
    $author->avatar = $a["avatar"];
    $author->save();
    $author->refresh();
    echo "  avatar: ", $was, "  ->  ", $author->avatar, "\n";
} else {
    echo "  avatar already ", $author->avatar, " — nothing to do.\n";
}

$onDisk = $author->avatar && Storage::disk("public")->exists($author->avatar);

echo "\n  ", $author->name, "  [/author/", $author->slug, "]\n";
echo "    avatar:   ", $author->avatar, "  ",
    ($onDisk ? Storage::disk("public")->size($author->avatar)." bytes on disk" : "MISSING ON DISK"), "\n";
echo "    about:    ", ($author->about ? mb_strimwidth($author->about, 0, 72, "...") : "(none)"), "  (untouched)\n";
echo "    articles: ", $author->articles()->count(), " (untouched)\n";

// The featured-authors strip is computed, not a list; report the pool this
// change lands him in rather than asserting it from memory.
$pool = Author::query()
    ->whereHas("articles", fn ($q) => $q->whereNotNull("published_at"))
    ->whereNotNull("avatar")
    ->where("avatar", "!=", "")
    ->whereNotIn("name", json_decode(File::get(base_path("database/data/fixes/batch188.json")), true)["excluded_names"])
    ->orderBy("name")
    ->get();

$inPool = $pool->contains(fn ($x) => $x->slug === $author->slug);

echo "\n    featured-author pool is now ", $pool->count(), "; ", $author->name,
    ($inPool ? " is in it." : " is NOT in it."), "\n";

// Batch 188 filed every featured author under Publications. Check the
// invariant still holds rather than assuming it does.
$stray = 0;

foreach ($pool as $p) {
    $stray += $p->articles()->whereDoesntHave("category", fn ($q) => $q->where("title", "Publications"))->count();
}

echo "    featured-author articles not under Publications: ", $stray,
    ($stray === 0 ? "  (batch 188 invariant holds)" : "   !! re-run batch 188"), "\n";

echo "\n  ", wordwrap($payload["photo_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["featured_pool_note"], 72, "\n  "), "\n";

if ($onDisk && $inPool) { echo "B190-OK\n"; }
'

run_tinker "set-avatar" "B190-OK" "$UPDATE_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 190 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
