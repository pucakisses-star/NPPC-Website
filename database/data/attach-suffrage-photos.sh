#!/usr/bin/env bash
#
# Attach public-domain portraits of 1917 National Woman's Party suffrage
# prisoners, cropped to head-and-shoulders from Library of Congress / Harris &
# Ewing / NWP Records images (all "Public domain" per Wikimedia Commons, mostly
# the LOC "Gallery of Suffrage Prisoners" numbered portraits).
#
# Source crops live in database/data/photos/suffrage/<key>.jpg. Each is matched
# to its prisoner by EXACT NAME (record slugs differ, e.g. Lavinia Lloyd Dock ->
# lavinia-lloyd-dock), then copied to storage/app/public/prisoners/<slug>.jpg.
#
# Only prisoners with NO existing photo are touched — anyone who already has a
# photo is skipped.
#
# Idempotent. Run AFTER prisoners:add-suffrage, from the repo root:
#   bash database/data/attach-suffrage-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

// crop-file key => exact prisoner name
$map = [
    "alice-paul"          => "Alice Paul",
    "rose-winslow"        => "Rose Winslow",
    "lucy-burns"          => "Lucy Burns",
    "julia-emory"         => "Julia Emory",
    "lavinia-dock"        => "Lavinia Lloyd Dock",
    "edith-ainge"         => "Edith Ainge",
    "maud-malone"         => "Maud Malone",
    "helena-hill-weed"    => "Helena Hill Weed",
    "virginia-arnold"     => "Virginia Arnold",
    "eunice-dana-brannan" => "Eunice Dana Brannan",
    "sue-shelton-white"   => "Sue Shelton White",
    "louise-bryant"       => "Louise Bryant",
    "anna-kelton-wiley"   => "Anna Kelton Wiley",
    "mary-nolan"          => "Mary A. Nolan",
];

$set = 0; $skipHas = 0; $skipMiss = 0;
foreach ($map as $key => $name) {
    $src = base_path("database/data/photos/suffrage/{$key}.jpg");
    if (! is_file($src)) { echo "  no source file for {$key}\n"; continue; }

    $p = Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", [strtolower($name)])->first();
    if (! $p) { echo "  not found: {$name} (run prisoners:add-suffrage first?)\n"; $skipMiss++; continue; }

    if (! empty($p->photo)) { echo "  skip (already has photo): {$p->name}\n"; $skipHas++; continue; }

    $dstRel = "prisoners/{$p->slug}.jpg";
    $dstAbs = storage_path("app/public/{$dstRel}");
    File::ensureDirectoryExists(dirname($dstAbs));
    File::copy($src, $dstAbs);
    $p->photo = $dstRel;
    $p->save();
    echo "  set photo: {$p->name} -> {$dstRel}\n";
    $set++;
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone. set={$set}, skipped_have_photo={$skipHas}, not_found={$skipMiss}\n";
'

echo
echo "Done. Suffrage portraits attached."
