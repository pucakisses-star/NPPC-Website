#!/usr/bin/env bash
#
# BATCH 72 -- Catherine Marie Kerkow: the photograph corrected to the
# DMV image the curator specified.
#
#   Batch 67 attached the wrong portrait: the FBI wanted listing
#   carries FOUR photographs, and the poster-PDF extraction shipped
#   the primary "Photograph taken in 1975" glasses portrait instead of
#   the curator-s requested kerkowdmv.jpg (fbi.gov/wanted/dt/
#   catherine-marie-kerkow/kerkowdmv.jpg — the license-style DMV
#   headshot). The FBI-s own API (api.fbi.gov/wanted/v1/list) lists
#   the image set — image, kerkowdmv.jpg, kerkow5.jpg, kerkow3.jpg —
#   matching the poster-s photo order, which identifies the DMV
#   image among the PDF extractions. fbi.gov itself and the Wayback
#   Machine both refuse this environment-s requests, so the DMV image
#   comes from the same official poster PDF, at its native embedded
#   resolution (513x686), resized to 525x700.
#
#   database/data/photos/catherine-marie-kerkow.jpg in the repo IS
#   now the DMV image, so batch 67-s copy step also ships the right
#   file on a fresh deploy. This batch FORCE-copies it over storage
#   (replacing the wrong portrait if batch 67 already ran) and makes
#   sure the photo field is attached.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-72.sh

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
echo "  Batch 72 — Kerkow: photo corrected to the DMV image"
echo "==================================================================="

fix_photo() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    if [ ! -f "database/data/photos/catherine-marie-kerkow.jpg" ]; then
        echo "source image missing — nothing to do"
        return 1
    fi

    cp -f "database/data/photos/catherine-marie-kerkow.jpg" "${DST_DIR}/catherine-marie-kerkow.jpg"
    echo "copied catherine-marie-kerkow.jpg (DMV image, replacing any prior file)"

    php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withUnderReview()->where("slug", "catherine-marie-kerkow")->first();

if (! $p) {
    echo "NOT FOUND: catherine-marie-kerkow\n";
    return;
}

$rel = "prisoners/catherine-marie-kerkow.jpg";

if ($p->photo !== $rel) {
    $p->photo = $rel;
    $p->save();
    echo "photo field set\n";
} else {
    echo "photo field already set — file replaced on disk\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-kerkow-dmv-photo" fix_photo

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 72 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
