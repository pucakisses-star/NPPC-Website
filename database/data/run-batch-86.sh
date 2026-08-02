#!/usr/bin/env bash
#
# BATCH 86 -- Charlie Hill: portrait attached.
#
#   Curator-supplied photograph (ibb.co/B2hQvr87): the close portrait
#   of Hill in later life in Cuba — gray hair, rectangular glasses —
#   cropped from the 1586x992 frame to 525x700. His record
#   (charlie-hill, the Republic of New Afrika member in Cuban exile)
#   had an empty photo slot; the attach fills empty slots only.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-86.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"
    shift
    echo
    echo "--- ${label}"
    if "$@"; then
        return 0
    fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}")
    return 0
}

echo "==================================================================="
echo "  Batch 86 — Charlie Hill: portrait"
echo "==================================================================="

attach_photo() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    if [ ! -f "database/data/photos/charlie-hill.jpg" ]; then
        echo "source image missing — nothing to do"
        return 1
    fi

    cp -f "database/data/photos/charlie-hill.jpg" "${DST_DIR}/charlie-hill.jpg"
    echo "copied charlie-hill.jpg"

    php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withUnderReview()->where("slug", "charlie-hill")->first();

if (! $p) {
    echo "NOT FOUND: charlie-hill\n";
    return;
}

$rel = "prisoners/charlie-hill.jpg";

if (! is_file(storage_path("app/public/".$rel))) {
    echo "no file in storage — skipped\n";
} elseif ($p->photo === $rel) {
    echo "already attached\n";
} elseif ($p->photo) {
    echo "has another photo (", $p->photo, ") — left alone\n";
} else {
    $p->photo = $rel;
    $p->save();
    echo "photo attached\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "attach-charlie-hill-photo" attach_photo

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 86 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
