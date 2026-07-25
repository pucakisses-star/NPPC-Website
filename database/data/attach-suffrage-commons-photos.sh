#!/usr/bin/env bash
#
# Attach public-domain portraits (Wikimedia Commons / LOC NWP Records) for
# suffragists whose LOC "Gallery" image was a group/scene shot. Cropped to
# head-and-shoulders. Source crops in database/data/photos/suffrage/<key>.jpg.
# Matched by exact name; only records with NO existing photo are touched.
#
# Idempotent. Run after prisoners:apply-suffrage-roster, from the repo root:
#   bash database/data/attach-suffrage-commons-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;
$map = [
    "bertha-moller"         => "Bertha Moller",
    "elsie-hill"            => "Elsie Hill",
    "sarah-tarleton-colvin" => "Sarah Tarleton Colvin",
    "vida-milholland"       => "Vida Milholland",
];
$set=0;$skipHas=0;$skipMiss=0;
foreach ($map as $key => $name) {
    $src = base_path("database/data/photos/suffrage/{$key}.jpg");
    if (! is_file($src)) { echo "  no source for {$key}\n"; continue; }
    $p = Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", [strtolower($name)])->first();
    if (! $p) { echo "  not found: {$name}\n"; $skipMiss++; continue; }
    if (! empty($p->photo)) { echo "  skip (has photo): {$p->name}\n"; $skipHas++; continue; }
    $dstRel = "prisoners/{$p->slug}.jpg";
    File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
    File::copy($src, storage_path("app/public/{$dstRel}"));
    $p->photo = $dstRel; $p->save();
    echo "  set photo: {$p->name}\n"; $set++;
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone. set={$set}, has_photo={$skipHas}, not_found={$skipMiss}\n";
'
echo; echo "Done."
