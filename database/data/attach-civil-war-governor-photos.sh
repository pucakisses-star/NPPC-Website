#!/usr/bin/env bash
#
# Attach portraits of the two Civil War governors held without charge in
# 1865 — cellmates at the Old Capitol military-prison complex, and now
# cross-referenced in each other{39}s records.
#
#   zebulon-vance   Brady-Handy portrait, Library of Congress Prints and
#                   Photographs Division, between 1870 and 1880. Cropped
#                   from the seated three-quarter original to head and
#                   shoulders, 524 x 700.
#   john-letcher    Brady portrait, U.S. National Archives, NARA 528418,
#                   circa 1860-1865. The original is a glass-plate scan
#                   with a light mount border and chipped corners; the
#                   crop is taken well inside the plate so none of that
#                   shows, 525 x 700.
#
# BOTH ARE PUBLIC DOMAIN — nineteenth-century photographs, no licence
# condition. Full provenance is in
# database/data/photos/CREDITS-civil-war-governors.md.
#
# The emulsion blemish on Letcher{39}s shoulder is left alone: it is a
# defect in the plate, not the scan, and retouching a historical
# negative is a different act from cropping one.
#
# LETCHER MUST EXIST FIRST. He is created by add-john-letcher.sh in
# batch 24, so this runs after it. If that has not been applied, this
# reports him as not found and still attaches the Vance portrait.
#
# Idempotent: copies the files and sets the fields to the same values
# every time.
#
# Run from the repo root:
#   bash database/data/attach-civil-war-governor-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

DST_DIR="storage/app/public/prisoners"
mkdir -p "$DST_DIR"

for slug in zebulon-vance john-letcher; do
    SRC="database/data/photos/${slug}.jpg"
    if [ -f "$SRC" ]; then
        cp -f "$SRC" "${DST_DIR}/${slug}.jpg"
        echo "copied ${slug}.jpg"
    else
        echo "MISSING SOURCE: $SRC"
    fi
done

php artisan tinker --execute='
use App\Models\Prisoner;

foreach (["zebulon-vance", "john-letcher"] as $slug) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) {
        echo "  NOT FOUND: {$slug}";
        if ($slug === "john-letcher") {
            echo " — run batch 24 (add-john-letcher.sh) first";
        }
        echo "\n";
        continue;
    }

    $rel = "prisoners/{$slug}.jpg";
    if (! is_file(storage_path("app/public/".$rel))) {
        echo "  FILE NOT IN STORAGE: {$rel}\n";
        continue;
    }

    $was = $p->photo;
    $p->photo = $rel;
    $p->save();
    echo "  ", str_pad($p->name, 18), " photo -> {$rel}",
         ($was && $was !== $rel ? "   (replaced {$was})" : ($was ? "   (unchanged)" : "   (was empty)")), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
