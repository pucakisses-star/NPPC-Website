#!/usr/bin/env bash
#
# BATCH 132 -- David Hemler portrait, per the curator.
#
#   The Swedish press portrait the curator supplied attaches into his
#   empty photo slot at the standard 525x700 panel. The source is
#   landscape, 683x430, so the panel is cropped around the head — he
#   sits well left of centre — and resampled up by 1.63x.
#
#   IDENTIFICATION IS WEAKER THAN USUAL, and the credits file says so
#   at length. The URL is an opaque Bonnier News CDN asset with no
#   caption, headline or byline, and no page carrying this exact
#   image was found. What supports it is the curator supplying it plus
#   consistency — a Swedish press portrait of a man in his late
#   forties, which is what Hemler was when he came forward in Uppsala
#   in June 2012 aged 49. That is consistency, not the two-fact
#   standard the batch 110 credits set out. See
#   database/data/CREDITS-batch-132.md, which also notes that the
#   rights position is weaker than the public-domain federal material
#   used elsewhere in the collection.
#
#   REQUIRES BATCH 131, which creates the david-hemler record. If it
#   has not run, this reports the record as missing and changes
#   nothing.
#
#   The photo attaches to an EMPTY slot only; an existing portrait is
#   never replaced. Idempotent.
#
# Run from the repo root, after git pull (after batch 131):
#   bash database/data/run-batch-132.sh

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
echo "  Batch 132 — David Hemler portrait"
echo "==================================================================="

attach_photo() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    if [ -f "database/data/photos/david-hemler.jpg" ]; then
        cp -f "database/data/photos/david-hemler.jpg" "${DST_DIR}/david-hemler.jpg"
        echo "portrait copied to ${DST_DIR}/david-hemler.jpg"
    else
        echo "!! portrait missing from database/data/photos — nothing to attach"
    fi

    php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withUnderReview()->where("slug", "david-hemler")->first();

if (! $p) {
    echo "david-hemler NOT FOUND — run batch 131 first, which creates the record. Nothing changed.\n";

    return;
}

echo "record: ", $p->name, "  [", $p->slug, "]\n";
echo "  before: photo=", ($p->photo ?: "(none)"), "\n";

$rel = "prisoners/david-hemler.jpg";

if (! is_file(storage_path("app/public/".$rel))) {
    echo "  the image is not on disk at storage/app/public/", $rel, " — nothing attached\n";

    return;
}

if ($p->photo && $p->photo !== $rel) {
    echo "  already carries a different portrait (", $p->photo, ") — left alone\n";
} elseif ($p->photo === $rel) {
    echo "  already attached; the file on disk was refreshed and the cache-busted URL picks it up\n";
} else {
    $p->photo = $rel;
    $p->save();
    echo "  photo attached\n";
}

echo "  after:  photo=", ($p->refresh()->photo ?: "(none)"), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "hemler-portrait" attach_photo

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 132 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
