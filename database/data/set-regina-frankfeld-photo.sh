#!/usr/bin/env bash
#
# Attach a portrait of Regina Frankfeld, cropped from the undated photo posted
# by Washington Area Spark on Flickr ("Frankfeld charged with communist
# conspiracy: 1951"), flickr.com/photos/washington_area_spark/37041316323.
# Head-and-shoulders crop.
#
# Idempotent. Run from the repo root:
#   bash database/data/set-regina-frankfeld-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;
$src = base_path("database/data/photos/nonfree/regina-frankfeld.jpg");
if (! is_file($src)) { echo "Source crop missing.\n"; return; }
$p = Prisoner::withoutGlobalScopes()
    ->where("slug", "regina-frankfeld")
    ->orWhereRaw("LOWER(name) = ?", ["regina frankfeld"])
    ->first();
if (! $p) { echo "Regina Frankfeld not found.\n"; return; }
$dstRel = "prisoners/{$p->slug}.jpg";
File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
File::copy($src, storage_path("app/public/{$dstRel}"));
$p->photo = $dstRel; $p->save();
echo "Set photo on {$p->name} -> {$dstRel}\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
echo; echo "Done."
