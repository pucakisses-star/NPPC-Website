#!/usr/bin/env bash
#
# BATCH 79 -- Joyce Tillerson and George Brown portraits, plus Black
# Liberation Army affiliation for the two McNairs.
#
#   PHOTOS — curator-supplied composite (ibb.co/xt4k7bCg): the four
#   1970s portraits of the Flight 841 defendants in France, cropped
#   per the curator-s positional identification — JOYCE TILLERSON is
#   the TOP-LEFT panel (hoop earring, beaded necklace), GEORGE BROWN
#   the BOTTOM-RIGHT (goatee, striped cardigan). Panels cut on the
#   composite-s own seams at 525x700. Both records had empty photo
#   slots; the attach fills empty slots only.
#
#   AFFILIATION — "Black Liberation Army" is appended for MELVIN
#   McNAIR and JEAN McNAIR (matched by old or renamed slug), joining
#   the Black Panther Party entry added in batch 78. Tillerson,
#   Brown and Wright already carry it.
#
# Idempotent throughout.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-79.sh

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
echo "  Batch 79 — Tillerson + Brown photos; BLA for the McNairs"
echo "==================================================================="

fix_batch() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    for slug in joyce-tillerson george-brown; do
        SRC="database/data/photos/${slug}.jpg"
        if [ -f "$SRC" ]; then
            cp -f "$SRC" "${DST_DIR}/${slug}.jpg"
            echo "copied ${slug}.jpg"
        fi
    done

    php artisan tinker --execute='
use App\Models\Prisoner;

foreach (["joyce-tillerson", "george-brown"] as $slug) {
    $p = Prisoner::withUnderReview()->where("slug", $slug)->first();

    if (! $p) {
        echo str_pad($slug, 26), "NOT FOUND\n";
        continue;
    }

    $rel = "prisoners/".$slug.".jpg";

    if (! is_file(storage_path("app/public/".$rel))) {
        echo str_pad($slug, 26), "no file in storage — skipped\n";
    } elseif ($p->photo === $rel) {
        echo str_pad($slug, 26), "already attached\n";
    } elseif ($p->photo) {
        echo str_pad($slug, 26), "has another photo — left alone\n";
    } else {
        $p->photo = $rel;
        $p->save();
        echo str_pad($slug, 26), "photo attached\n";
    }
}

$groups = [
    ["melvin-mcnair"],
    ["jean-carol-allen-mcnair", "jean-mcnair"],
];

foreach ($groups as $slugs) {
    $p = Prisoner::withUnderReview()->whereIn("slug", $slugs)->first();

    if (! $p) {
        echo str_pad($slugs[0], 26), "NOT FOUND\n";
        continue;
    }

    $aff = $p->affiliation ?? [];

    if (in_array("Black Liberation Army", $aff, true)) {
        echo str_pad($p->slug, 26), "already has BLA\n";
        continue;
    }

    $aff[] = "Black Liberation Army";
    $p->affiliation = array_values($aff);
    $p->save();
    echo str_pad($p->slug, 26), "affiliation now: ", implode(", ", $p->affiliation), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-photos-and-affiliations" fix_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 79 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
