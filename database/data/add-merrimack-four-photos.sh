#!/usr/bin/env bash
#
# Merrimack Four: booking photographs and full middle names.
#
# Three of the four had no photograph at all, and none of the four
# carried a middle name despite the press and the court records using
# them.
#
# PHOTOGRAPHS. Three come from the Merrimack Police booking set
# published by Fox News as a single three-panel image (left to right:
# Sophie Marika Ross, Calla Mairead Walsh, Bridget Irene Shergalis),
# split here on the white separators the source image carries at x=397
# and x=832. Paige Belangers is her own Merrimack Police booking
# photograph, published separately by Patch.
#
# IDENTIFICATION IS CORROBORATED, NOT ASSUMED. Two independent checks
# confirm the left-to-right order:
#
#   1. The record for Calla Walsh ALREADY carried a photograph, and it
#      is the middle panel of this very image at lower resolution
#      (271x350 against the 427x675 here). The middle panel therefore
#      identifies itself, which fixes the left and right panels by
#      position.
#   2. A separate Patch courtroom collage captioned "Bridget Irene
#      Shergalis, upper right ... Paige Belanger is lower left" shows
#      the same dark-haired woman as the right panel here and the same
#      woman as the Belanger booking photograph.
#
# Calla Walsh gets the higher-resolution crop in place of the small one
# already on her record; it is the same photograph, so nothing about her
# portrait changes except its sharpness.
#
# MIDDLE NAMES from the court records and the Attorney Generals release:
#   Sophie Marika Ross, Calla Mairead Walsh, Bridget Irene Shergalis.
# PAIGE BELANGER GETS NO MIDDLE NAME -- none was supplied and none is
# invented.
#
# The photos live in database/data/photos/merrimack-four/ and are copied
# onto the public disk under the slug, the same convention the Zimmer
# import uses.
#
# Idempotent: re-running re-copies the same files and rewrites the same
# names. Run from the repo root:
#   bash database/data/add-merrimack-four-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

File::ensureDirectoryExists(storage_path("app/public/prisoners"));

$people = [
    "sophie-ross"       => ["first" => "Sophie",  "middle" => "Marika", "last" => "Ross"],
    "calla-walsh"       => ["first" => "Calla",   "middle" => "Mairead", "last" => "Walsh"],
    "bridget-shergalis" => ["first" => "Bridget", "middle" => "Irene",  "last" => "Shergalis"],
    "paige-belanger"    => ["first" => "Paige",   "middle" => null,     "last" => "Belanger"],
];

$attached = 0;
foreach ($people as $slug => $name) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) {
        echo "  NOT FOUND: {$slug}\n";
        continue;
    }

    $p->first_name = $name["first"];
    $p->last_name  = $name["last"];
    if ($name["middle"] !== null) {
        $p->middle_name = $name["middle"];
    }

    $src = database_path("data/photos/merrimack-four/{$slug}.jpg");
    if (is_file($src)) {
        $dest = "prisoners/{$slug}.jpg";
        File::copy($src, storage_path("app/public/".$dest), true);
        touch(storage_path("app/public/".$dest));
        $p->photo = $dest;
        $attached++;
    } else {
        echo "  photo file missing: {$src}\n";
    }

    $p->save();

    $full = trim($p->first_name." ".($p->middle_name ?: "")." ".$p->last_name);
    echo "  ".str_pad($slug, 20)." {$full}   photo: ".($p->photo ?: "(none)")."\n";
}

echo "\n{$attached} photo(s) attached.\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
