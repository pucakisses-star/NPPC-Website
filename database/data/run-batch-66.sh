#!/usr/bin/env bash
#
# BATCH 66 -- portraits for the currently imprisoned: a systematic
# photo hunt over every in-custody record without a photograph.
#
# Sixty-one records were swept. TWENTY-SEVEN identified photographs
# attach here — provenance and anchors, one per photo, in
# database/data/photos/custody2026/CREDITS.md. The strongest are the
# two official corrections photos fetched by inmate number (NC DAC
# 0802041 for Joseph Shine-White Stewart; Ohio DRC A173-245 for Namir
# Abdul-Mateen, under his committed name James Were); the rest are
# captioned news photographs, sheriff-released booking photos with
# positional captions, and self-labeled campaign or agency images.
#
# NINE finds were HELD BACK for failing the identification or quality
# bar (weak anchors, a courtroom sketch, a prejudicial evidence photo,
# a photo-of-a-photo — all listed in the credits with their leads),
# and TWENTY-FIVE people have no publicly anchored photograph at all;
# their best future leads are recorded. Drop a slug-named file into
# database/data/photos/custody2026/ and re-run to complete any of
# them.
#
# THE ATTACH LOOP ONLY FILLS EMPTY PHOTO SLOTS — it never replaces an
# existing portrait.
#
# The credits also flag three DATA issues the hunt surfaced, beyond
# photos: carlos-coleman appears to have been out of custody since
# 2013 with dismissed federal charges; tony-alexander-hamilton is no
# longer in Utah DOC custody; armando-gomez's sentence is reported as
# 70 months, not 120. Left unchanged pending the curator's call.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-66.sh

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
echo "  Batch 66 — portraits for the currently imprisoned"
echo "==================================================================="

attach_photos() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    for SRC in database/data/photos/custody2026/*.jpg; do
        [ -e "$SRC" ] || continue
        cp -f "$SRC" "${DST_DIR}/$(basename "$SRC")"
    done
    echo "copied $(ls database/data/photos/custody2026/*.jpg | wc -l) portrait(s)"

    php artisan tinker --execute='
use App\Models\Prisoner;

$dir = base_path("database/data/photos/custody2026");
$attached = 0;
$skipped = 0;

foreach (glob($dir."/*.jpg") as $file) {
    $slug = basename($file, ".jpg");
    $p = Prisoner::withUnderReview()->where("slug", $slug)->first();

    if (! $p) {
        echo str_pad($slug, 34), "NOT FOUND\n";
        continue;
    }

    $rel = "prisoners/".$slug.".jpg";

    if ($p->photo === $rel) {
        $skipped++;
    } elseif ($p->photo) {
        echo str_pad($slug, 34), "has another photo — left alone\n";
        $skipped++;
    } elseif (! is_file(storage_path("app/public/".$rel))) {
        echo str_pad($slug, 34), "no file in storage — skipped\n";
    } else {
        $p->photo = $rel;
        $p->save();
        echo str_pad($slug, 34), "photo attached\n";
        $attached++;
    }
}

echo "\nattached: ", $attached, "   already set or protected: ", $skipped, "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "attach-custody-2026-photos" attach_photos

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 66 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
