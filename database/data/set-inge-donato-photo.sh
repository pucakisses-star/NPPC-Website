#!/usr/bin/env bash
#
# Force-replace Inge (Rose) Donato's photo with the re-cropped portrait from the
# NWTRCC April 2008 group photo, overriding any existing photo.
#
# Idempotent. Run from the repo root:
#   bash database/data/set-inge-donato-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;
$src = base_path("database/data/photos/riy/inge-donato.jpg");
if (! is_file($src)) { echo "Source crop missing.\n"; return; }
$p = Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", ["inge donato"])->first();
if (! $p) { echo "Inge Donato not found.\n"; return; }
$dstRel = "prisoners/{$p->slug}.jpg";
File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
File::copy($src, storage_path("app/public/{$dstRel}"));
$p->photo = $dstRel; $p->save();
echo "Replaced photo on {$p->name} -> {$dstRel}\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
echo; echo "Done."
