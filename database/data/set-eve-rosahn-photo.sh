#!/usr/bin/env bash
#
# Eve Rosahn: attach the courtroom sketch portrait held by the University of
# Virginia Law Library Archives and Special Collections
# (https://archives.law.virginia.edu/resources/184198/object/97604), cropped
# from the full-resolution IIIF master to trim the blank paper margins.
#
# Idempotent (force-replaces the photo). Run from the repo root:
#   bash database/data/set-eve-rosahn-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$p = Prisoner::withoutGlobalScopes()->where("slug", "eve-rosahn")->first()
    ?? Prisoner::withoutGlobalScopes()->where("name", "like", "%Rosahn%")->first();
if (! $p) { echo "NOT FOUND: Eve Rosahn\n"; exit(1); }

$src = base_path("database/data/photos/eve-rosahn.jpg");
if (! is_file($src)) { echo "PHOTO SOURCE MISSING: database/data/photos/eve-rosahn.jpg\n"; exit(1); }

$dstRel = "prisoners/{$p->slug}.jpg";
File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
File::copy($src, storage_path("app/public/{$dstRel}"));
$p->photo = $dstRel;
$p->save();

echo "{$p->name}: photo set -> {$dstRel}\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
