#!/usr/bin/env bash
#
# BATCH 161 -- César Andreu Iglesias: the portrait, on a fair-use
# determination by the curator.
#
#   Batch 156 declined to copy this image and recorded why: it is
#   hosted on the English Wikipedia rather than Commons, is
#   categorised as non-free, and its file page warns that uses
#   elsewhere may be copyright infringement. The curator has
#   considered that and determined the use is fair. That is a
#   judgement for the publisher of a site to make, and the reasoning
#   is a recognised one, so it is recorded here rather than argued
#   with. A publisher relying on fair use should have a written
#   rationale; the payload carries it.
#
#   IN SHORT: the subject died in 1976 so no free equivalent can be
#   created; Commons holds no freely licensed image of him; the use is
#   identification in a non-commercial biographical entry; and the
#   file is used at its published resolution of 260 by 382 pixels and
#   under 9 KB, already small enough that it cannot substitute for any
#   licensing of the original.
#
#   This does not make the determination correct and it is not legal
#   advice. If a rights holder objects the fix is one field: the file
#   stays on disk, and setting photo back to null restores the
#   previous state exactly.
#
#   Idempotent.
#
# Run from the repo root, after git pull (after batch 160):
#   bash database/data/run-batch-161.sh

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
echo "  Batch 161 — César Andreu Iglesias: the portrait"
echo "==================================================================="

FILE="cesar-andreu-iglesias.jpg"
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
        echo "  !! NOT reachable through the public symlink — run php artisan storage:link"
        return 1
    fi
}

update_record() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch161.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withUnderReview()->where("slug", $payload["slug"])->first();

if (! $p) { echo "  ", $payload["slug"], " NOT FOUND — nothing changed.\n"; return; }

echo "  ", $p->name, "\n";
echo "  photo before: ", ($p->photo ?: "(none)"), "\n";

$p->photo = $payload["photo_path"];
$p->save();
$p->refresh();

echo "  photo after:  ", $p->photo, "\n";

$f = storage_path("app/public/".$p->photo);

echo "  file on disk: ", (is_file($f) ? filesize($f)." bytes" : "MISSING — the copy step did not run"), "\n";

echo "\n  RIGHTS BASIS RECORDED WITH THIS CHANGE\n";
echo "  ", wordwrap($payload["note"], 84, "\n  "), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "install-photo" install_photo
run "update-record" update_record

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 161 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
