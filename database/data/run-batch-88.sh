#!/usr/bin/env bash
#
# BATCH 88 -- Hollis Watkins: the AP portrait attached.
#
#   The curator-supplied photograph: the Associated Press portrait
#   used by his obituary coverage (static.independent.co.uk,
#   Obit_Hollis_Watkins_72330.jpg — the image the batch 87 dossier
#   identified). Fetched at full frame and cropped to 525x700. Fills
#   an empty photo slot only; drop-in completion for the batch 87
#   record.
#
# Run from the repo root, after git pull (batch 87 must have run):
#   bash database/data/run-batch-88.sh

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
echo "  Batch 88 — Hollis Watkins: AP portrait"
echo "==================================================================="

attach_photo() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    if [ ! -f "database/data/photos/hollis-watkins.jpg" ]; then
        echo "source image missing — nothing to do"
        return 1
    fi

    cp -f "database/data/photos/hollis-watkins.jpg" "${DST_DIR}/hollis-watkins.jpg"
    echo "copied hollis-watkins.jpg"

    php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withUnderReview()->where("slug", "hollis-watkins")->first();

if (! $p) {
    echo "NOT FOUND: hollis-watkins — run batch 87 first\n";
    return;
}

$rel = "prisoners/hollis-watkins.jpg";

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

run "attach-hollis-watkins-photo" attach_photo

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 88 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
