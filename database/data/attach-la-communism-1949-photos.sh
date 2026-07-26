#!/usr/bin/env bash
#
# Attach portraits cropped from the 1949 Los Angeles press photo "Ben Dobbs,
# Henry Steinberg and Samuel H. Kashinowitz after being sentenced in Communism
# investigation, Calif., 1949" (UCLA Library Special Collections, ark
# 21198/zz0002vqh8). The three handcuffed defendants, left to right, are Dobbs,
# Steinberg, Kashinowitz. Matched by exact name; only records with NO existing
# photo are touched.
#
# Idempotent. Run from the repo root:
#   bash database/data/attach-la-communism-1949-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;
$map = [
    "ben-dobbs"           => "Ben Dobbs",
    "henry-steinberg"     => "Henry Steinberg",
    "samuel-kashinowitz"  => "Samuel Harry Kasinowitz",
];
$set=0;$skipHas=0;$skipMiss=0;
foreach ($map as $key => $name) {
    $src = base_path("database/data/photos/nonfree/{$key}.jpg");
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
