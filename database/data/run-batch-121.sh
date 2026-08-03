#!/usr/bin/env bash
#
# BATCH 121 -- Dean Wyrzykowski, per the curator:
#
#   - middle name set to Guzman
#   - the photograph the curator supplied (holding a rescued beagle)
#     REPLACES his existing portrait: it overwrites the same
#     prisoners/dean-wyrzykowski.jpg path, and the photo URL is
#     cache-busted by file modification time, so the swap shows
#     immediately.
#
# Run from the repo root, after git pull (after batch 120):
#   bash database/data/run-batch-121.sh

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
echo "  Batch 121 — Dean Wyrzykowski middle name + replacement photo"
echo "==================================================================="

fix_batch() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    if [ -f "database/data/photos/dean-wyrzykowski.jpg" ]; then
        cp -f "database/data/photos/dean-wyrzykowski.jpg" "${DST_DIR}/dean-wyrzykowski.jpg"
        echo "replacement photo copied over the existing file"
    fi

    php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withUnderReview()->where("slug", "dean-wyrzykowski")->first();

if (! $p) { echo "dean-wyrzykowski NOT FOUND\n"; return; }

$notes = [];

if ($p->middle_name !== "Guzman") {
    $p->middle_name = "Guzman";
    $notes[] = "middle_name=Guzman";
}

$rel = "prisoners/dean-wyrzykowski.jpg";
if (is_file(storage_path("app/public/".$rel)) && $p->photo !== $rel) {
    $p->photo = $rel;
    $notes[] = "photo path set";
}

if ($notes) { $p->save(); }

echo implode("; ", $notes ?: ["already correct"]),
    " (photo file overwritten on disk — the cache-busted URL serves the new image)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "wyrzykowski-update" fix_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 121 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
