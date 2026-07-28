#!/usr/bin/env bash
#
# Eqbal Ahmad -- crop the print border off his photo and set his race.
#
# PHOTO. The stored image was a scan of a photographic print with its white
# border left on, roughly 12 to 15 pixels on every side. The border grey runs
# about 190 to 200 against picture content around 80 to 125, so it was found
# by profile rather than by a pure-white threshold. Crop box 12,15 to 248,355
# of the 261x370 original -- the entire photograph is kept, only the frame
# goes. No resampling, no ratio imposed.
#
# RACE. Set to "Middle Eastern" at the site owner's direction (the record had
# "Asian"). The value is already in use in the database (Babar Ahmad). For the
# record: Ahmad was born in Bihar, India and was Pakistani-American, which
# most taxonomies would class as South Asian -- noted here so the choice is
# visible, applied as directed.
#
# The photo replacement overwrites the existing image at the same path and
# touches the file so the ?v=mtime cache buster moves.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-eqbal-ahmad-photo-race.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$matches = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->where("slug", "eqbal-ahmad")
        ->orWhereRaw("LOWER(name) = ?", ["eqbal ahmad"]))
    ->get();

if ($matches->isEmpty()) { echo "NOT FOUND: Eqbal Ahmad\n"; exit(1); }
if ($matches->count() > 1) {
    echo "ABORT: {$matches->count()} records match. Refusing to guess:\n";
    foreach ($matches as $m) { echo "  {$m->slug}  {$m->name}\n"; }
    exit(1);
}

$p = $matches->first();

echo "BEFORE  race=".($p->race ?: "-")."  photo=".($p->photo ?: "(none)")."\n";

$src = base_path("database/data/photos/eqbal-ahmad.jpg");
if (! is_file($src)) { echo "MISSING SOURCE: database/data/photos/eqbal-ahmad.jpg\n"; exit(1); }

$dstRel = $p->photo ?: "prisoners/{$p->slug}.jpg";
File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
File::copy($src, storage_path("app/public/{$dstRel}"));
touch(storage_path("app/public/{$dstRel}"));   // bust the ?v=mtime photo cache
$p->photo = $dstRel;
$p->race = "Middle Eastern";
$p->save();

echo "AFTER   race={$p->race}  photo={$p->photo} (border cropped, cache buster moved)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
