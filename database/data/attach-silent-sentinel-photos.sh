#!/usr/bin/env bash
#
# SIX SILENT SENTINEL PORTRAITS.
#
# Provenance, rights and the reasoning behind each choice are in
# database/data/photos/CREDITS-silent-sentinels.md. Every image was
# verified against its ARCHIVAL CAPTION, not just its filename, before
# being used.
#
#   julia-emory       LOC mnwp.274011. Caption names Baltimore and her
#                     father the state senator.
#   agnes-chase       Smithsonian. Caption carries her life dates, which
#                     match this record exactly.
#   anna-herkner      Maryland Women’s Heritage Center. THE PRINTED
#                     IMAGE IS CAPTIONED "MISS ANNA HERKNER", which is
#                     independent typographic confirmation of the
#                     spelling batch 30 corrected.
#   bertha-moller     Hennepin County Library via DPLA. Caption names
#                     Minnesota and the National Woman’s Party. The LOC
#                     "Mrs. Bertha C. Moller" portrait is deliberately
#                     NOT used -- see the credits file.
#   berthe-arnold     LOC mnwp.147007, the exact resource cited in the
#                     dossier. Caption names Colorado Springs.
#   catherine-boyle   University of Delaware, A. N. Sanborn studio.
#
# RUN BATCH 30 FIRST. Anna Herkner is renamed there from "Anne Herkimer",
# and the slug is rebuilt with the name. This script looks up BOTH slugs
# so it still works either way, but if batch 30 has not run the file will
# land on the old slug and will need re-attaching afterwards.
#
# WHY SOME ARE MISSING. Dr. Sarah Hunt Lockrey’s portrait is behind a
# Cloudflare block at hsp.org that refuses this environment outright, and
# Anna Ginsberg Hayutin’s is a family group in a finding aid rather than
# a served portrait. Both are listed below and reported as missing rather
# than silently skipped, so the gap stays visible. Drop the files into
# database/data/photos/ and re-run to attach them.
#
# The Magee group photograph is NOT here on purpose: batch 30 declined to
# settle that identity, and attaching a portrait would assert it.
#
# LOC and the Smithsonian both serve Cloudflare challenges or 403s to
# non-interactive clients, so the LOC and Smithsonian images were taken
# from their Wikimedia Commons mirrors, which carry the same archival
# captions and digital ids.
#
# Idempotent: re-running copies the same files and rewrites the same
# paths.
#
# Run from the repo root:
#   bash database/data/attach-silent-sentinel-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

DST_DIR="storage/app/public/prisoners"
mkdir -p "$DST_DIR"

for slug in julia-emory agnes-chase anna-herkner bertha-moller berthe-arnold catherine-boyle dr-sarah-h-lockrey anna-ginsberg; do
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

// slug in storage  =>  slugs to try in the database, in order
$targets = [
    "julia-emory" => ["julia-emory"],
    "agnes-chase" => ["agnes-chase"],
    // renamed in batch 30; try the new slug first, then the old one
    "anna-herkner" => ["anna-herkner", "anne-herkimer"],
    "bertha-moller" => ["bertha-moller"],
    "berthe-arnold" => ["berthe-arnold"],
    "catherine-boyle" => ["catherine-boyle"],
    "dr-sarah-h-lockrey" => ["dr-sarah-h-lockrey"],
    "anna-ginsberg" => ["anna-ginsberg"],
];

$attached = 0;
$noFile = 0;
$noRecord = 0;

foreach ($targets as $file => $slugs) {
    $p = Prisoner::withoutGlobalScopes()->whereIn("slug", $slugs)->first();

    if (! $p) {
        echo "  NOT FOUND: ", implode(" / ", $slugs), "\n";
        $noRecord++;
        continue;
    }

    $rel = "prisoners/{$file}.jpg";

    if (! is_file(storage_path("app/public/".$rel))) {
        echo "  ", str_pad($p->name, 26), " no image on disk yet — left without a photo\n";
        $noFile++;
        continue;
    }

    $was = $p->photo;
    $p->photo = $rel;
    $p->save();
    $attached++;

    echo "  ", str_pad($p->name, 26), " -> {$rel}",
         ($was && $was !== $rel ? "   (replaced {$was})" : ($was ? "   (unchanged)" : "   (was empty)")),
         "   [slug: {$p->slug}]\n";
}

echo "\nPhotos attached:      {$attached}\n";
echo "Awaiting an image:    {$noFile}\n";
echo "Records not found:    {$noRecord}\n";

$cohort = Prisoner::withoutGlobalScopes()->where("description", "like", "%Silent Sentinels%")->get();
echo "Silent Sentinels cohort: ", $cohort->count(),
     "  with a photo: ", $cohort->filter(fn ($x) => $x->photo)->count(), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
