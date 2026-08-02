#!/usr/bin/env bash
#
# BATCH 104 -- portraits for two Boeing Heath blockade defendants.
#
#   NANCY EPLING — the self-portrait published by the Athens County
#   Independent (August 2025, its investigation of her termination
#   from Passion Works over her Palestine activism); the caption
#   names her directly. Source frame is an exact 3:4, resized to
#   525x700.
#
#   GRAHAM BALL — from The Beachcomber (Beachwood High School,
#   January 12, 2024, the holiday charitable-drives story); the
#   caption identifies "USCRI Development Associate Graham Ball"
#   standing at right, and the crop isolates him at 525x700.
#
#   Both records had EMPTY photo slots; the attach step only fills
#   empty slots and reports if a photo is already present.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-104.sh

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
echo "  Batch 104 — Epling + Ball portraits"
echo "==================================================================="

fix_batch() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    for slug in nancy-epling graham-ball; do
        if [ -f "database/data/photos/${slug}.jpg" ]; then
            cp -n "database/data/photos/${slug}.jpg" "${DST_DIR}/${slug}.jpg" 2>/dev/null \
                || cp "database/data/photos/${slug}.jpg" "${DST_DIR}/${slug}.jpg"
            echo "copied ${slug}.jpg"
        fi
    done

    php artisan tinker --execute='
use App\Models\Prisoner;

foreach (["nancy-epling", "graham-ball"] as $slug) {
    $p = Prisoner::withUnderReview()->where("slug", $slug)->first();

    if (! $p) {
        echo str_pad($slug, 24), "NOT FOUND\n";
        continue;
    }

    $rel = "prisoners/".$slug.".jpg";

    if (! is_file(storage_path("app/public/".$rel))) {
        echo str_pad($slug, 24), "file missing on disk — skipped\n";
        continue;
    }

    if ($p->photo === $rel) {
        echo str_pad($slug, 24), "already attached\n";
        continue;
    }

    if ($p->photo) {
        echo str_pad($slug, 24), "has a DIFFERENT photo (", $p->photo, ") — left alone\n";
        continue;
    }

    $p->photo = $rel;
    $p->save();
    echo str_pad($slug, 24), "photo attached\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "attach-epling-ball-portraits" fix_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 104 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
