#!/usr/bin/env bash
#
# TWO MORE SILENT SENTINEL PORTRAITS.
#
# Provenance and rights are in the "Round two" section of
# database/data/photos/CREDITS-silent-sentinels.md. Both were verified
# against their archival captions before use.
#
#   ernestine-hara      LOC mnwp.151010. Caption names her and describes
#                       the tricolor sash and banner; the studio credit
#                       "HARRIS & EWING" is legible in the print, which
#                       matches the photographer named in the dossier.
#   josephine-bennett   Harriet Beecher Stowe Center via Connecticut
#                       History, captioned "Josephine Bennett in
#                       Washington, D.C. in January 1919" -- the month
#                       and city of her arrest.
#
# BENNETT IS NOT THE IMAGE THE DOSSIER LINKED. That one is the c. 1914
# photograph with her daughters, 382x599 at source and 191x300 as linked.
# This is from the same collection and the same article, more than twice
# the size, and dated to the month she was jailed. Swap it if the family
# photograph is wanted specifically.
#
# ELIZABETH GREEN KALB IS DELIBERATELY ABSENT. She already carries the
# correct image: her stored photo is the same LOC Harris & Ewing portrait
# (mnwp.153001) the dossier cites, already cropped at 640x847, which is
# above the 525x700 target. Re-cropping from the larger Commons original
# would gain very little and would churn a correct record, so it is left
# alone.
#
# ELLEN WINSOR IS LISTED AND WILL REPORT MISSING. Her photograph is at
# the Historical Society of Pennsylvania, which refuses this environment
# outright, and she is not on Wikimedia Commons. It is also a two-person
# photograph -- she is on the left, with her sister Rebecca Winsor Evans
# -- so it needs cropping as well as fetching. Drop the file in as
# database/data/photos/ellen-winsor.jpg and re-run.
#
# RUN BATCH 32 BEFORE OR AFTER; order does not matter here. None of these
# three slugs is renamed in batch 32, so unlike the Herkner case in batch
# 30 there is no slug to wait for.
#
# Idempotent: re-running copies the same files and rewrites the same
# paths.
#
# Run from the repo root:
#   bash database/data/attach-silent-sentinel-photos-2.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

DST_DIR="storage/app/public/prisoners"
mkdir -p "$DST_DIR"

for slug in ernestine-hara josephine-bennett ellen-winsor; do
    SRC="database/data/photos/${slug}.jpg"
    if [ -f "$SRC" ]; then
        cp -f "$SRC" "${DST_DIR}/${slug}.jpg"
        echo "copied ${slug}.jpg"
    else
        echo "no source image for ${slug} — skipped (see CREDITS-silent-sentinels.md)"
    fi
done

php artisan tinker --execute='
use App\Models\Prisoner;

$slugs = ["ernestine-hara", "josephine-bennett", "ellen-winsor"];

$attached = 0;
$noFile = 0;
$noRecord = 0;

foreach ($slugs as $slug) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();

    if (! $p) {
        echo "  NOT FOUND: {$slug}\n";
        $noRecord++;
        continue;
    }

    $rel = "prisoners/{$slug}.jpg";

    if (! is_file(storage_path("app/public/".$rel))) {
        echo "  ", str_pad($p->name, 24), " no image on disk yet — left without a photo\n";
        $noFile++;
        continue;
    }

    $was = $p->photo;
    $p->photo = $rel;
    $p->save();
    $attached++;

    echo "  ", str_pad($p->name, 24), " -> {$rel}",
         ($was && $was !== $rel ? "   (replaced {$was})" : ($was ? "   (unchanged)" : "   (was empty)")), "\n";
}

echo "\nPhotos attached:   {$attached}\n";
echo "Awaiting an image: {$noFile}\n";
echo "Records not found: {$noRecord}\n";

$cohort = Prisoner::withoutGlobalScopes()->where("description", "like", "%Silent Sentinels%")->get();
echo "Silent Sentinels cohort: ", $cohort->count(),
     "  with a photo: ", $cohort->filter(fn ($x) => $x->photo)->count(),
     "  with a birthdate: ", $cohort->filter(fn ($x) => $x->birthdate)->count(), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
