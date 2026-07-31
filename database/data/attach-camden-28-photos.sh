#!/usr/bin/env bash
#
# CAMDEN 28 PORTRAITS -- six attached, five pursued and not obtained.
#
# Provenance and rights for every image, and the reasons for every gap,
# are in database/data/photos/CREDITS-camden-28.md. The short version:
#
#   father-michael-doyle   his diocesan obituary portrait
#   edward-mcgowan         his obituary photograph, with his fiddle
#   john-swinglish         the memorial photograph -- and note that the
#                          handcuffed man flashing a peace sign on the
#                          documentary poster IS his arrest photo
#   keith-forsyth          WHYY, at the Media FBI office site, 50th
#                          anniversary, named in the caption
#   anne-dunham            the 2017 SDPB studio photograph, identified
#                          against Pommersheim's faculty portrait
#   kathleen-ridolfi       the NCIP founders photograph, identified
#                          against her confirmed SCU headshot
#
# ROBERT WILLIAMSON and MARGARET INNESS are listed and will report
# missing: his obituary is Cloudflare-blocked from this environment and
# hers carries no portrait at all (stock bird art, despite the dossier's
# note). Drop files in as database/data/photos/<slug>.jpg and re-run.
#
# NOBODY IS ATTACHED ON A NAME MATCH ALONE -- every identification above
# is anchored to a caption, an obituary, or a face-match against an
# already-confirmed image, and the anchor is recorded in the credits
# file. That is the lesson of Jacob Tori wearing Jacob Riis's face.
#
# Idempotent: files are copied over themselves harmlessly, and a record
# already carrying the right path reports as already attached.
#
# Run from the repo root, after git pull:
#   bash database/data/attach-camden-28-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

DST_DIR="storage/app/public/prisoners"
mkdir -p "$DST_DIR"

SLUGS="father-michael-doyle edward-mcgowan john-swinglish keith-forsyth anne-dunham kathleen-ridolfi robert-williamson margaret-inness"

for slug in $SLUGS; do
    SRC="database/data/photos/${slug}.jpg"
    if [ -f "$SRC" ]; then
        cp -f "$SRC" "${DST_DIR}/${slug}.jpg"
        echo "copied ${slug}.jpg"
    else
        echo "no source image for ${slug} — skipped (see CREDITS-camden-28.md)"
    fi
done

php artisan tinker --execute='
use App\Models\Prisoner;

$slugs = [
    "father-michael-doyle",
    "edward-mcgowan",
    "john-swinglish",
    "keith-forsyth",
    "anne-dunham",
    "kathleen-ridolfi",
    "robert-williamson",
    "margaret-inness",
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
        echo "  ", str_pad($p->name, 26), " no image on disk yet — left as is\n";
        $noFile++;
        continue;
    }

    if ($p->photo === $rel) {
        echo "  ", str_pad($p->name, 26), " already attached\n";
        $attached++;
        continue;
    }

    $was = $p->photo;
    $p->photo = $rel;
    $p->save();
    $attached++;

    echo "  ", str_pad($p->name, 26), " -> {$rel}",
         ($was ? "   (replaced {$was})" : "   (was empty)"), "\n";
}

echo "\nPhotos attached:   {$attached}\n";
echo "Awaiting an image: {$noFile}\n";
echo "Records not found: {$noRecord}\n";

$cohort = Prisoner::withoutGlobalScopes()->get()->filter(
    fn ($p) => in_array("Camden 28", (array) $p->affiliation)
);
echo "\nCamden 28 cohort: ", $cohort->count(),
     "  with a photo: ", $cohort->filter(fn ($x) => $x->photo)->count(), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
