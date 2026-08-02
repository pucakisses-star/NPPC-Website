#!/usr/bin/env bash
#
# BATCH 91 -- Ralph Goodwin: photo replaced with the second KOAT
# graphic-s portrait-oriented panel.
#
#   The curator-supplied second KOAT graphic (kubrick.htvapps.com
#   31645952) carries three labeled panels — Finney, Hill, GOODWIN —
#   and its Goodwin panel is portrait-oriented, so it crops to a true
#   525x700 without the blurred-fill letterbox batch 89 needed for
#   the first graphic-s square panel. The panel is cut at its own
#   edges (a sliver of the graphic-s black frame shows top and
#   bottom); note the SOURCE graphic itself crops at his chin — that
#   edge is in the original, not the crop.
#
#   This batch FORCE-COPIES the new file over storage (replacing the
#   batch 89 letterboxed version if it was applied) and makes sure
#   the photo field is set. The repo file is the new version, so
#   batch 89 also ships this crop on a fresh deploy.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-91.sh

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
echo "  Batch 91 — Ralph Goodwin: replacement portrait"
echo "==================================================================="

fix_photo() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    if [ ! -f "database/data/photos/ralph-goodwin.jpg" ]; then
        echo "source image missing — nothing to do"
        return 1
    fi

    cp -f "database/data/photos/ralph-goodwin.jpg" "${DST_DIR}/ralph-goodwin.jpg"
    echo "copied ralph-goodwin.jpg (replacing any prior file)"

    php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withUnderReview()->where("slug", "ralph-goodwin")->first();

if (! $p) {
    echo "NOT FOUND: ralph-goodwin\n";
    return;
}

$rel = "prisoners/ralph-goodwin.jpg";

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

run "fix-ralph-goodwin-photo" fix_photo

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 91 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
