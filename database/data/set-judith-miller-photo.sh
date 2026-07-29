#!/usr/bin/env bash
#
# Judith Miller -- attach her portrait.
#
# THIS MUST RUN AFTER database/data/add-free-expression-five.sh, which
# is what creates her record. If it runs first the script says so and
# stops without doing damage.
#
# THE IMAGE IS FREELY LICENSED, unlike most portraits on this site: it
# is CC BY-SA 2.0 by Ben P L of Provo, Utah, from Wikimedia Commons, so
# it lives in the free database/data/photos/ folder rather than
# photos/nonfree/ and is credited in CREDITS-wikipedia.md. ATTRIBUTION
# IS REQUIRED by the licence, which the credits file supplies.
#
# The source is 2875x3238 and already framed as a head-and-shoulders
# shot of her speaking at a microphone; it is only downscaled, to
# 621x700, with no cropping.
#
# Idempotent. Run from the repo root:
#   bash database/data/set-judith-miller-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$p = Prisoner::withoutGlobalScopes()->where("slug", "judith-miller")->first();
if (! $p) {
    echo "NOT FOUND: judith-miller\n";
    echo "Run database/data/add-free-expression-five.sh first -- that is what creates her record.\n";
    exit(1);
}

$src = database_path("data/photos/judith-miller.jpg");
if (! is_file($src)) {
    echo "PHOTO FILE MISSING: {$src}\n";
    exit(1);
}

$was = $p->photo ?: "(none)";
File::ensureDirectoryExists(storage_path("app/public/prisoners"));
$dest = "prisoners/judith-miller.jpg";
File::copy($src, storage_path("app/public/".$dest), true);
touch(storage_path("app/public/".$dest));
$p->photo = $dest;
$p->save();

echo "Judith Miller  [{$p->slug}]\n";
echo "  was:  {$was}\n";
echo "  now:  {$p->photo}   ".filesize(storage_path("app/public/".$dest))." bytes\n";
echo "  licence: CC BY-SA 2.0, Ben P L (attribution required, see CREDITS-wikipedia.md)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
