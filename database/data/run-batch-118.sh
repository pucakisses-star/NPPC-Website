#!/usr/bin/env bash
#
# BATCH 118 -- per the curator:
#
#   ARTHUR TAYLOR gets his record: the UMWA District 2 organizer
#   arrested at Somerset with Powers Hapgood around April 15, 1922
#   (in custody two days before the April 17 Boswell action, per the
#   New Majority account), free again by April 17 — at most about
#   two days held. Created via prisoner:add, which refuses
#   duplicates by name.
#
#   POWERS HAPGOOD gets the biography the curator supplied verbatim
#   as a REPLACEMENT (one where/were typo fixed) — an explicit
#   curator-directed replacement of the description; the detailed
#   chronology lives on in his eight case rows. He also gets the
#   press portrait the curator supplied, into the EMPTY photo slot
#   only.
#
# Run from the repo root, after git pull (after batch 117):
#   bash database/data/run-batch-118.sh

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
echo "  Batch 118 — Arthur Taylor record + Hapgood bio and portrait"
echo "==================================================================="

create_taylor() {
    php artisan prisoner:add "$(cat database/data/fixes/arthur-taylor.json)"
}

hapgood_updates() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    if [ -f "database/data/photos/powers-hapgood.jpg" ]; then
        cp -f "database/data/photos/powers-hapgood.jpg" "${DST_DIR}/powers-hapgood.jpg"
        echo "copied powers-hapgood.jpg"
    fi

    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch118.json")), true);

foreach ($payload["set_description"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->first();

    echo "\nBIO ", $row["slug"], "\n";

    if (! $p) { echo "  NOT FOUND — skipped\n"; continue; }

    if (trim((string) $p->description) === trim($row["description"])) {
        echo "  already set\n";
    } else {
        $p->description = $row["description"];
        $p->save();
        echo "  description replaced per the curator\n";
    }
}

foreach ($payload["photos"] as $slug) {
    $p = Prisoner::withUnderReview()->where("slug", $slug)->first();

    if (! $p) { echo str_pad($slug, 20), "NOT FOUND\n"; continue; }

    $rel = "prisoners/".$slug.".jpg";

    if (! is_file(storage_path("app/public/".$rel))) {
        echo str_pad($slug, 20), "file missing on disk — skipped\n";
        continue;
    }

    if ($p->photo === $rel) { echo str_pad($slug, 20), "already attached\n"; continue; }
    if ($p->photo) { echo str_pad($slug, 20), "has a DIFFERENT photo — left alone\n"; continue; }

    $p->photo = $rel;
    $p->save();
    echo str_pad($slug, 20), "photo attached\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "create-arthur-taylor" create_taylor
run "hapgood-bio-and-portrait" hapgood_updates

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 118 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
