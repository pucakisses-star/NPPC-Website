#!/usr/bin/env bash
#
# Kolton Krottinger -- attach his portrait.
#
# The record had no image. The photograph supplied by the site owner is
# a VFW portrait: he is wearing the cap of Riley Stephens Memorial VFW
# Post 7835 of Granbury, Texas, against a United States flag, which
# matches the record independently -- it already describes him as a
# Navy veteran from Granbury.
#
# Cropped from the 2047x2048 square original to 553x700 head and
# shoulders, trimming the flag at the sides while keeping the cap and
# the shoulders whole. Copyrighted, so it lives in photos/nonfree/ and
# is credited in database/data/photos/CREDITS-nonfree.md.
#
# Idempotent. Run from the repo root:
#   bash database/data/set-kolton-krottinger-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$p = Prisoner::withoutGlobalScopes()->where("slug", "kolton-krottinger")->first();
if (! $p) {
    echo "NOT FOUND: kolton-krottinger\n";
    exit(1);
}

$src = database_path("data/photos/nonfree/kolton-krottinger.jpg");
if (! is_file($src)) {
    echo "PHOTO FILE MISSING: {$src}\n";
    exit(1);
}

$was = $p->photo ?: "(none)";
File::ensureDirectoryExists(storage_path("app/public/prisoners"));
$dest = "prisoners/kolton-krottinger.jpg";
File::copy($src, storage_path("app/public/".$dest), true);
touch(storage_path("app/public/".$dest));
$p->photo = $dest;
$p->save();

echo "Kolton Krottinger  [{$p->slug}]\n";
echo "  was:  {$was}\n";
echo "  now:  {$p->photo}   ".filesize(storage_path("app/public/".$dest))." bytes\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
