#!/usr/bin/env bash
#
# BATCH 65 -- booking photos for the Ridglan Farms three and Gabriela
# Saldana.
#
# PROVENANCE AND ANCHORS:
#
#   ASWANI, WYRZYKOWSKI, LUNSKY — Dane County Jail booking
#   photographs, from the four-mugshot composite WSAW published with
#   its April 22, 2026 charging story, whose caption reads "(L-R)
#   Dean F Wyrzykowski, Michelle Lunsky, Wayne Hsiung, Aditya
#   Aswani" — a positional identification, corroborated by Wayne
#   Hsiung's independently recognizable face sitting in the stated
#   third position. Panels one, two and four are cropped at 525x700;
#   Hsiung is not in this database and his panel is not used.
#
#   GABRIELA SALDANA — the Miami-Dade booking photograph as published
#   by WSVN 7News with its April 2026 arrest story; the image file
#   itself is named "041626-Twenty-three-year-old-Gabriela-Saldana",
#   and the mugshot panel is cropped out of the station's frame at
#   525x700.
#
# The attach loop only fills EMPTY photo slots — none of the four has
# a photograph today, and a curator-chosen portrait would never be
# replaced by a re-run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-65.sh

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
echo "  Batch 65 — Ridglan three + Saldana booking photos"
echo "==================================================================="

attach_photos() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    for slug in aditya-aswani dean-wyrzykowski michelle-lunsky gabriela-saldana; do
        SRC="database/data/photos/${slug}.jpg"
        if [ -f "$SRC" ]; then
            cp -f "$SRC" "${DST_DIR}/${slug}.jpg"
            echo "copied ${slug}.jpg"
        else
            echo "no source image for ${slug} — skipped"
        fi
    done

    php artisan tinker --execute='
use App\Models\Prisoner;

foreach (["aditya-aswani", "dean-wyrzykowski", "michelle-lunsky", "gabriela-saldana"] as $slug) {
    $p = Prisoner::withUnderReview()->where("slug", $slug)->first();

    if (! $p) {
        echo str_pad($slug, 24), "NOT FOUND\n";
        continue;
    }

    $rel = "prisoners/".$slug.".jpg";

    if (! is_file(storage_path("app/public/".$rel))) {
        echo str_pad($slug, 24), "no file in storage — skipped\n";
    } elseif ($p->photo === $rel) {
        echo str_pad($slug, 24), "already attached\n";
    } elseif ($p->photo) {
        echo str_pad($slug, 24), "has another photo (", $p->photo, ") — left alone\n";
    } else {
        $p->photo = $rel;
        $p->save();
        echo str_pad($slug, 24), "photo attached\n";
    }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "attach-ridglan-saldana-photos" attach_photos

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 65 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
