#!/usr/bin/env bash
#
# ROUND THREE of the Silent Sentinel portraits -- the seven identified in
# batch 34 and never fetched.
#
# TWO ARE ATTACHED. FIVE ARE NOT, and the run prints every one of the
# five so the outstanding list stays visible in the terminal and not just
# in a markdown file. Provenance for all seven is in the "Round three"
# section of database/data/photos/CREDITS-silent-sentinels.md.
#
# THE BLOCK ON loc.gov IS GONE. Batches 30 to 32 recorded that the
# Library of Congress refused this environment and worked around it by
# taking Wikimedia Commons mirrors instead. Both loc.gov and
# tile.loc.gov now answer normally, including the JSON catalogue API and
# the MASTER TIFFs under tile.loc.gov/storage-services/master/. Fetch
# straight from the source from here on; the Commons detour is no longer
# needed. hsp.org and siarchives.si.edu still return 403.
#
#   amy-juengling     ATTACHED. Library of Congress mnwp.276023, the
#                     Harris & Ewing photograph of the picket line of
#                     November 10, 1917 -- the day she was arrested.
#   belle-sheinberg   ATTACHED. The same negative. Same day, same line.
#
# BOTH IDENTIFICATIONS COME FROM THE LOC CAPTION, which names all nine
# women left to right: Catherine Martinette, Elizabeth Kent, Mary
# Bartlett Dixon, C. T. Robertson, Cora Week, AMY JUENGLING, Hattie
# Kruger, BELLE SHEINBERG, Julia Emory. Juengling is sixth and Sheinberg
# eighth, and both crops were checked against the independently made
# Wikimedia crops of the same plate before being cut. Julia Emory, ninth
# in the same line, already carries a portrait from batch 31.
#
#   Sheinberg is soft. She stands PARTLY BEHIND Hattie Kruger and
#   occupies only about 220 pixels of a 3035-pixel plate, so reaching
#   525x700 means enlarging about two and a half times. It is kept on
#   the same reasoning as Anna Herkner's newsprint halftone in batch 31:
#   authentic, captioned, and the alternative is no portrait at all.
#   Her face is in focus and centred. Replace it if a studio portrait
#   turns up.
#
# ------------------------------------------------------------------
# THE FIVE NOT ATTACHED
# ------------------------------------------------------------------
#
# betsy-graves-reyneau  FOUND, VERIFIED, AND DELIBERATELY NOT USED.
#   Los Angeles Times, April 5, 1948, via UCLA Digital Library
#   (ark:/21198/zz0002nbnb): she stands beside her own portrait of
#   George Washington Carver, 2154x2962, unambiguous. IT IS CC BY 4.0,
#   NOT PUBLIC DOMAIN. Every other portrait on this site is public
#   domain, and prisoner records have no photo credit field -- the
#   template renders a bare <img> with no caption -- so there is nowhere
#   to put the attribution the licence requires. Adding a photo_credit
#   column and a caption line under the portrait would unlock this image
#   and any other CC BY archival photograph; that is a schema change and
#   is not made here on its own initiative.
#
# phoebe-c-munnecke     The LOC portrait index lists "Munnecke, Phoebe C.
# beatrice-kinkead      (I:155, 1 image)" and "Kinkead, Beatrice (I:153,
#                       1 image)", so the prints exist -- but they were
#                       NOT DIGITISED. Folder 153 runs Kalb, Keller,
#                       Kelley, King, Koonce, La Follette, and folder 155
#                       runs Moller, Moore, Morey, and neither name
#                       appears. Both need a reproduction order from the
#                       Manuscript Division.
#
# ada-louise-davenport-kendall
#                       Two problems, not one. The index has "Kendall,
#                       Mrs. Frederick W. (I:153, 1 image)" and nothing
#                       under Ada. That entry is also not digitised, and
#                       identifying Mrs. Frederick W. Kendall with Ada
#                       Louise Davenport Kendall needs a source rather
#                       than an inference from a shared surname. Even if
#                       the plate were online it should not be attached
#                       until the identification is made.
#
# rebecca-winsor-evans  Historical Society of Pennsylvania, the same
#                       two-sister photograph noted for Ellen Winsor in
#                       batch 32 -- Ellen on the left, Rebecca on the
#                       right. hsp.org still refuses this environment on
#                       every path tried, and the Wayback Machine was
#                       rate-limiting at the time of writing. Needs an
#                       ordinary browser, then a crop to the RIGHT-hand
#                       figure. Drop it in as
#                       database/data/photos/rebecca-winsor-evans.jpg and
#                       re-run; the script already lists her.
#
# All five slugs are listed below on purpose, so the run reports them as
# awaiting an image rather than silently covering two out of seven.
#
# Idempotent: re-running copies the same files and compares before
# writing. Records with no source file keep whatever photo they have.
#
# Run from the repo root:
#   bash database/data/attach-silent-sentinel-photos-3.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

DST_DIR="storage/app/public/prisoners"
mkdir -p "$DST_DIR"

SLUGS="amy-juengling belle-sheinberg rebecca-winsor-evans ada-louise-davenport-kendall phoebe-c-munnecke beatrice-kinkead betsy-graves-reyneau"

for slug in $SLUGS; do
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

$slugs = [
    "amy-juengling",
    "belle-sheinberg",
    "rebecca-winsor-evans",
    "ada-louise-davenport-kendall",
    "phoebe-c-munnecke",
    "beatrice-kinkead",
    "betsy-graves-reyneau",
];

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
        echo "  ", str_pad($p->name, 30), " no image on disk yet — left without a photo\n";
        $noFile++;
        continue;
    }

    $was = $p->photo;

    if ($was === $rel) {
        echo "  ", str_pad($p->name, 30), " already attached\n";
        $attached++;
        continue;
    }

    $p->photo = $rel;
    $p->save();
    $attached++;

    echo "  ", str_pad($p->name, 30), " -> {$rel}",
         ($was ? "   (replaced {$was})" : "   (was empty)"), "\n";
}

echo "\nPhotos attached:   {$attached}\n";
echo "Awaiting an image: {$noFile}\n";
echo "Records not found: {$noRecord}\n";

$cohort = Prisoner::withoutGlobalScopes()->where("description", "like", "%Silent Sentinels%")->get();
echo "\nSilent Sentinels cohort: ", $cohort->count(),
     "  with a photo: ", $cohort->filter(fn ($x) => $x->photo)->count(),
     "  with a birthdate: ", $cohort->filter(fn ($x) => $x->birthdate)->count(), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
