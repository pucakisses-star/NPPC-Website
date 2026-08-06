#!/usr/bin/env bash
#
# BATCH 187 -- James R. Bennett: a biography and a photograph for an
# author page that already existed bare.
#
#   THE PAGE WAS ALREADY THERE. /author/james-r-bennett exists and
#   carries three articles, including "Political Trials and Prisoners in
#   the United States" — but no about text and no photograph, so it
#   rendered a bare initial placeholder. This fills in exactly what was
#   supplied: the biography and the picture.
#
#   THE BIOGRAPHY is the curator's text: retired Professor of English
#   who taught at the University of Arkansas, and co-founder of the
#   Arkansas ACLU.
#
#   THE PHOTOGRAPH comes from omnicenter.org — the OMNI Center for
#   Peace, Justice & Ecology. In the original frame he wears a name tag
#   reading "Dick Bennett, Founder", his byname, which is what confirms
#   the identification. Cropped to a 600x600 head-and-shoulders square
#   with the name tag out of frame, because the site renders author
#   avatars in circles and a chest-height tag would sit half-clipped on
#   every byline.
#
#   IF THE AUTHOR IS SOMEHOW ABSENT it is created rather than skipped,
#   so the batch converges on any environment; on the live site it will
#   find and update the existing record, articles untouched.
#
#   Idempotent: the file is copied only when it differs, and the fields
#   are set only when they differ.
#
# Run from the repo root, after git pull (after batch 186):
#   bash database/data/run-batch-187.sh

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
echo "  Batch 187 — James R. Bennett, biography and photograph"
echo "==================================================================="

SRC="database/data/files/authors/james-r-bennett.jpg"
DEST_DIR="storage/app/public/authors"

echo
echo "--- install-photo"
install_ok=1
mkdir -p "$DEST_DIR"
if [ ! -f "$SRC" ]; then
    echo "  missing source file: $SRC"; install_ok=0
else
    dest="$DEST_DIR/james-r-bennett.jpg"
    if [ -f "$dest" ] && cmp -s "$SRC" "$dest"; then
        echo "  james-r-bennett.jpg — already installed, identical"
    else
        [ -f "$dest" ] && echo "  james-r-bennett.jpg — $(stat -c%s "$dest") bytes -> $(stat -c%s "$SRC")" \
                       || echo "  james-r-bennett.jpg — new file"
        cp "$SRC" "$dest"
    fi
    if [ ! -e "public/storage/authors/james-r-bennett.jpg" ]; then
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

$payload = json_decode(File::get(base_path("database/data/fixes/batch187.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$a = $payload["author"];

$author = Author::where("slug", $a["slug"])->first();

if (! $author) {
    $author = Author::create(["name" => $a["name"]]);
    echo "  author was absent — created [", $author->slug, "]\n";
}

$changed = [];

if ($author->about !== $a["about"]) { $author->about = $a["about"]; $changed[] = "about"; }
if ($author->avatar !== $a["avatar"]) { $author->avatar = $a["avatar"]; $changed[] = "avatar"; }

if ($changed) { $author->save(); }

$author->refresh();

$onDisk = $author->avatar && Storage::disk("public")->exists($author->avatar);

echo "  ", $author->name, "  [/author/", $author->slug, "]\n";
echo "    set:      ", ($changed ? implode(", ", $changed) : "nothing — already correct"), "\n";
echo "    about:    ", $author->about, "\n";
echo "    avatar:   ", $author->avatar, "  ",
    ($onDisk ? Storage::disk("public")->size($author->avatar)." bytes on disk" : "MISSING ON DISK"), "\n";
echo "    articles: ", $author->articles()->count(), " (untouched)\n";
echo "\n  ", wordwrap($payload["photo_note"], 72, "\n  "), "\n";

if ($onDisk) { echo "B187-OK\n"; }
'

run_tinker "update-author" "B187-OK" "$UPDATE_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 187 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
