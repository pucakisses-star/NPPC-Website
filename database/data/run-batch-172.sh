#!/usr/bin/env bash
#
# BATCH 172 -- four Stop Cop City portraits cropped out of the broadcast
# frames they were saved as.
#
#   WHAT WAS WRONG. Geoffrey Parsons profile carried a 932x524 image: a
#   16:9 television frame with his mugshot standing in the middle at
#   about 413 pixels wide, and the two side panels filled with a blurred,
#   stretched copy of the same picture. More than half the file was
#   smeared filler, and on a profile page the face rendered small
#   between two grey blurs.
#
#   IT WAS NOT JUST HIM. All 34 photographs in prisoners/cop-city were
#   checked; six are 16:9 broadcast frames rather than portraits.
#
#   FOUR ARE FIXED HERE, by finding the bars rather than guessing at
#   them. The blurred panels carry almost no vertical detail, so a
#   column-by-column high-frequency scan separates them from the real
#   picture cleanly, and the boundary is then pulled in two pixels to
#   drop the blend seam. Each keeps its native resolution — no upscaling,
#   no re-encoding beyond the single save.
#
#     francis-carroll    932x524 -> 471x524
#     geoffrey-parsons   932x524 -> 413x524
#     graham-evatt       932x524 -> 367x524
#     ivan-ferguson      932x524 -> 343x524
#
#   THE ASPECT RATIOS ARE LEFT UNEVEN ON PURPOSE, from 0.66 to 0.90. The
#   source mugshots were framed differently and forcing them to a common
#   ratio would mean cutting into faces. This folder already runs from
#   0.66 to 1.00, so uneven is the convention rather than a departure
#   from it.
#
#   TWO CANNOT BE CROPPED and are not touched. Emily Murphy frame is
#   zoomed INTO the mugshot, cutting off the top of her head and her
#   chin, so the whole file is picture and there are no bars to remove.
#   Henri Feola is worse: the frame ends just below the eyes, so the
#   lower half of the face is not in the file at all, and it carries a
#   station watermark as well. Cropping cannot put back what the frame
#   cut off. Both need a replacement source image, and shipping a tighter
#   crop of a broken frame would only hide that.
#
#   NO DATABASE CHANGES. The files are replaced in place at the paths the
#   records already point to. Prisoner::photoUrl() appends a ?v= stamp
#   from the file modification time, so a replacement at the same path
#   busts the cache on its own.
#
#   Idempotent: each file is copied only when it differs from the source.
#
# Run from the repo root, after git pull (after batch 171):
#   bash database/data/run-batch-172.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"; shift
    echo; echo "--- ${label}"
    if "$@"; then return 0; fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}"); return 0
}

echo "==================================================================="
echo "  Batch 172 — cropping four Cop City portraits out of 16:9 frames"
echo "==================================================================="

SRC_DIR="database/data/files/cop-city"
DEST_DIR="storage/app/public/prisoners/cop-city"

install_photos() {
    mkdir -p "$DEST_DIR"
    local n=0

    for src in "$SRC_DIR"/*.jpg; do
        [ -e "$src" ] || { echo "  no source files in $SRC_DIR"; return 1; }
        local base dest
        base="$(basename "$src")"
        dest="$DEST_DIR/$base"

        head -c 2 "$src" | od -An -tx1 | grep -q 'ff d8' \
            || { echo "  !! $base is not a JPEG"; return 1; }

        if [ -f "$dest" ] && cmp -s "$src" "$dest"; then
            echo "  $base — already installed, identical"
        else
            if [ -f "$dest" ]; then
                echo "  $base — replacing $(stat -c%s "$dest") bytes with $(stat -c%s "$src")"
            else
                echo "  $base — new file (the record pointed at a missing image)"
            fi
            cp "$src" "$dest"
        fi

        [ -e "public/storage/prisoners/cop-city/$base" ] \
            || { echo "  !! not reachable through the public symlink — run php artisan storage:link"; return 1; }
        n=$((n + 1))
    done

    echo "  $n file(s) in place"
}

verify_records() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch172.json")), true);

if (! $payload) { echo "Could not read the payload — nothing verified.\n"; return; }

echo "  ", str_pad("record", 22), str_pad("photo column", 40), "on disk\n";
echo "  ", str_repeat("-", 76), "\n";

$bad = 0;

foreach ($payload["rows"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->first();

    if (! $p) { echo "  ", $row["slug"], " — record not found\n"; $bad++; continue; }

    // Nothing is written here. The point is to confirm the column still points
    // at the file this batch replaced, since the crop only takes effect if it
    // landed on the path the record actually reads.
    $ok = $p->photo === $row["photo_path"];
    $path = $p->photo ? Storage::disk("public")->path($p->photo) : null;
    $size = ($path && is_file($path)) ? filesize($path) : null;

    echo "  ", str_pad($row["slug"], 22), str_pad((string) $p->photo, 40),
        ($size !== null ? $size." bytes" : "MISSING"),
        ($ok ? "" : "   !! column does not match the payload path"), "\n";

    if (! $ok || $size === null) { $bad++; }
}

echo "\n  ", ($bad === 0 ? "all four records point at the cropped files" : $bad." problem(s) above"), "\n";

echo "\n  NOT CROPPED — these need a replacement source, not a crop:\n";

foreach ($payload["cannot_crop"] as $c) {
    echo "    ", $c["slug"], " (", $c["size"][0], "x", $c["size"][1], ")\n";
    echo "      ", wordwrap($c["why"], 70, "\n      "), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "install-crops" install_photos
run "verify-records" verify_records

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 172 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "Check the four profiles. The photo_url accessor stamps ?v= from the"
echo "file mtime, so the new crops should appear without a cache purge."
