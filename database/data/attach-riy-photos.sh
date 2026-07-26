#!/usr/bin/env bash
#
# Attach portraits of the three Restored Israel of YAHWEH tax-case defendants,
# cropped from the group photo in the NWTRCC newsletter "More Than a Paycheck"
# (April 2008, nwtrcc.org), captioned l-to-r: Robin Harper, Kevin McKee, Peter
# Goldberger, Rose Donato, Joe Donato (photo courtesy of Peter Goldberger). The
# embedded image was a negative; it was inverted to a positive before cropping.
#
# Matched by exact name; only records with NO existing photo are touched.
# Idempotent. Run from the repo root:
#   bash database/data/attach-riy-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$map = [
    "inge-donato"    => "Inge Donato",
    "joseph-donato"  => "Joseph Donato",
    "kevin-mckee"    => "Kevin McKee",
];

$set = 0; $skipHas = 0; $skipMiss = 0;
foreach ($map as $key => $name) {
    $src = base_path("database/data/photos/riy/{$key}.jpg");
    if (! is_file($src)) { echo "  no source for {$key}\n"; continue; }
    $p = Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", [strtolower($name)])->first();
    if (! $p) { echo "  not found: {$name}\n"; $skipMiss++; continue; }
    if (! empty($p->photo)) { echo "  skip (has photo): {$p->name}\n"; $skipHas++; continue; }
    $dstRel = "prisoners/{$p->slug}.jpg";
    File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
    File::copy($src, storage_path("app/public/{$dstRel}"));
    $p->photo = $dstRel; $p->save();
    echo "  set photo: {$p->name} -> {$dstRel}\n"; $set++;
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone. set={$set}, has_photo={$skipHas}, not_found={$skipMiss}\n";
'
echo; echo "Done."
