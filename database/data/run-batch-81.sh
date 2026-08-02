#!/usr/bin/env bash
#
# BATCH 81 -- photo corrections: Melvin McNair re-cropped as a
# headshot; the Tillerson and Brown panels trimmed of their seam
# lines.
#
#   MELVIN McNAIR — his stored photo was the full wide street frame
#   (2943x1656, Caen); recropped to a 525x700 headshot centered on
#   his face.
#
#   JOYCE TILLERSON and GEORGE BROWN — the batch 79 panel crops
#   carried slivers of the composite-s white seam lines (Tillerson:
#   2px at top, 6px at bottom; Brown: the blurred vertical seam at
#   the left edge and 4px of white at the bottom). The seams were
#   located by row/column brightness scans and the crops re-cut just
#   inside the photo content — nothing but the white strips was
#   trimmed — and re-verified (no edge row/column brighter than 240).
#
#   This batch FORCE-COPIES all three over storage (they are
#   corrections, not first attachments) and makes sure each photo
#   field is set. The repo files under database/data/photos/ are the
#   corrected versions, so batch 79 also ships the fixed crops on a
#   fresh deploy.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-81.sh

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
echo "  Batch 81 — photo corrections: McNair headshot, seam trims"
echo "==================================================================="

fix_photos() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    for slug in melvin-mcnair joyce-tillerson george-brown; do
        SRC="database/data/photos/${slug}.jpg"
        if [ -f "$SRC" ]; then
            cp -f "$SRC" "${DST_DIR}/${slug}.jpg"
            echo "copied ${slug}.jpg (corrected crop, replacing prior file)"
        else
            echo "no source image for ${slug} — skipped"
        fi
    done

    php artisan tinker --execute='
use App\Models\Prisoner;

foreach (["melvin-mcnair", "joyce-tillerson", "george-brown"] as $slug) {
    $p = Prisoner::withUnderReview()->where("slug", $slug)->first();

    if (! $p) {
        echo str_pad($slug, 26), "NOT FOUND\n";
        continue;
    }

    $rel = "prisoners/".$slug.".jpg";

    if (! is_file(storage_path("app/public/".$rel))) {
        echo str_pad($slug, 26), "no file in storage — skipped\n";
    } elseif ($p->photo !== $rel) {
        $p->photo = $rel;
        $p->save();
        echo str_pad($slug, 26), "photo field set\n";
    } else {
        echo str_pad($slug, 26), "photo field already set — file replaced on disk\n";
    }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-photo-corrections" fix_photos

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 81 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
