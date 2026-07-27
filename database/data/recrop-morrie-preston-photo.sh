#!/usr/bin/env bash
#
# Re-crop Morrie R. Preston portrait: 80px trimmed from the left edge so his
# face sits in the centre of the frame instead of off to the right
# (548x585 -> 468x585). Installs the re-cropped source over the live file.
#
# Idempotent (force-replaces the photo). Run from the repo root:
#   bash database/data/recrop-morrie-preston-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$p = Prisoner::withoutGlobalScopes()->where("slug", "morrie-r-preston")->first()
    ?? Prisoner::withoutGlobalScopes()->where("name", "like", "%Preston%")->first();
if (! $p) { echo "NOT FOUND: Morrie R. Preston\n"; exit(1); }

$src = base_path("database/data/photos/morrie-r-preston.jpg");
if (! is_file($src)) { echo "PHOTO SOURCE MISSING: database/data/photos/morrie-r-preston.jpg\n"; exit(1); }

$dstRel = $p->photo ?: "prisoners/{$p->slug}.jpg";
File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
File::copy($src, storage_path("app/public/{$dstRel}"));
$p->photo = $dstRel;
$p->save();

echo "{$p->name}: re-cropped photo installed -> {$dstRel}\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
