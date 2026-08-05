#!/usr/bin/env bash
#
# BATCH 160 -- Isidore Begun: the 1937 portrait.
#
#   THE LICENCE WAS CHECKED AND THIS ONE IS CLEAR. The image sits on
#   Wikimedia Commons, not on the English Wikipedia, and its file page
#   states it is in the public domain in the United States, published
#   there between 1931 and 1977 without a copyright notice. Author:
#   the Daily Worker. Source: the Daily Worker and Daily World
#   Photographs Collection. That is a different situation from César
#   Andreu Iglesias in batch 156, whose Wikipedia portrait is non-free
#   and was therefore not copied.
#
#   THE UPGRADE. The existing photograph is 272 by 404 pixels. The
#   replacement is 1099 by 1400, resized from a 2289 by 2916 original
#   of 3.7 MB — far too heavy to serve — and re-encoded at quality 86
#   to about 300 KB. The EXIF was stripped in the process, which also
#   drops the editing-software name and timestamp the uploader left in
#   the file.
#
#   THE OLD FILE IS NOT OVERWRITTEN. The new portrait is installed
#   under its own name and the record repointed, so
#   prisoners/isidore-begun.jpg stays on disk and this can be undone by
#   resetting one field.
#
#   Idempotent: re-running copies the same bytes and sets the same
#   path.
#
# Run from the repo root, after git pull (after batch 159):
#   bash database/data/run-batch-160.sh

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
echo "  Batch 160 — Isidore Begun: the 1937 portrait"
echo "==================================================================="

FILE="isidore-begun-1937.jpg"
SRC="database/data/files/${FILE}"
DEST="storage/app/public/prisoners/${FILE}"

install_photo() {
    if [ ! -f "$SRC" ]; then echo "  source missing: $SRC"; return 1; fi

    mkdir -p storage/app/public/prisoners

    if [ -f "$DEST" ] && cmp -s "$SRC" "$DEST"; then
        echo "  already installed, identical: $DEST"
    else
        cp "$SRC" "$DEST"
        echo "  installed: $DEST"
    fi

    ls -l "$DEST"

    head -c 2 "$DEST" | od -An -tx1 | grep -q 'ff d8' \
        && echo "  header check: JPEG" \
        || { echo "  !! header check FAILED — not a JPEG"; return 1; }

    if [ -e "public/storage/prisoners/${FILE}" ]; then
        echo "  reachable at public/storage/prisoners/${FILE}"
    else
        echo "  !! NOT reachable through the public symlink"
        echo "     run: php artisan storage:link"
        return 1
    fi
}

update_record() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch160.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withUnderReview()->where("slug", $payload["slug"])->first();

if (! $p) { echo "  ", $payload["slug"], " NOT FOUND — nothing changed.\n"; return; }

$was = $p->photo;

echo "  ", $p->name, "\n";
echo "  photo before: ", ($was ?: "(none)"), "\n";

$p->photo = $payload["photo_path"];
$p->save();
$p->refresh();

echo "  photo after:  ", $p->photo, "\n";

$old = storage_path("app/public/".$was);

if ($was && $was !== $payload["photo_path"] && is_file($old)) {
    echo "  previous file kept on disk: ", $was, " (", filesize($old), " bytes)\n";
    echo "  to undo, set photo back to that path — nothing was deleted.\n";
}

$new = storage_path("app/public/".$p->photo);

echo "\n  new file on disk: ", (is_file($new) ? filesize($new)." bytes" : "MISSING — the copy step did not run"), "\n";
echo "  public url:       ", $p->photo_url ?? ("/storage/".$p->photo), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "install-photo" install_photo
run "update-record" update_record

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 160 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
