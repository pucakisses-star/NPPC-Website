#!/usr/bin/env bash
#
# Portrait for John Theodore "Ted" Glick -- the eighth defendant in the
# Harrisburg 8 indictment, whose case the judge severed from the others when he
# insisted on representing himself, and whom the government then declined to
# retry.
#
# Source: Washington Area Spark on Flickr, "Glick Harrisburg case severed from
# others: 1972", an Associated Press photograph from the D.C. Library
# Washington Star Collection, taken outside the courthouse in Harrisburg on
# May 25, 1971.
#   https://www.flickr.com/photos/washington_area_spark/49023584791/
#
# A single-subject press portrait, so no identification guesswork: he is the
# only person in focus and the caption names only him.
#
# The scan carried the white page margins of the archive print and a small tear
# at the top edge. Both were cropped off (content box detected at 40,44 to
# 633,979; the crop starts below the tear at y=62) and the contrast was
# stretched slightly. Nothing else was altered, and the image is used at its
# native resolution with no upscaling.
#
# Only attached where the record has no photo; FORCE=1 replaces regardless.
#
# Idempotent. Run from the repo root:
#   bash database/data/attach-john-theodore-glick-photo.sh
#   FORCE=1 bash database/data/attach-john-theodore-glick-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

FORCE="${FORCE:-0}" php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$force = getenv("FORCE") === "1";

$matches = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->whereIn("slug", ["john-theodore-glick", "ted-glick", "john-glick"])
        ->orWhereRaw("LOWER(name) IN (?, ?)", ["john theodore glick", "ted glick"]))
    ->get();

if ($matches->isEmpty()) { echo "NOT FOUND: John Theodore Glick\n"; exit(1); }
if ($matches->count() > 1) {
    echo "ABORT: {$matches->count()} records match. Refusing to guess:\n";
    foreach ($matches as $m) { echo "  {$m->slug}  {$m->name}\n"; }
    exit(1);
}

$p = $matches->first();

if ($p->photo && ! $force) {
    echo "{$p->name}: already has a photo ({$p->photo}) -- left alone. FORCE=1 to replace.\n";
    exit(0);
}

$src = base_path("database/data/photos/john-theodore-glick.jpg");
if (! is_file($src)) { echo "MISSING SOURCE: database/data/photos/john-theodore-glick.jpg\n"; exit(1); }

$dstRel = "prisoners/{$p->slug}.jpg";
File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
File::copy($src, storage_path("app/public/{$dstRel}"));
touch(storage_path("app/public/{$dstRel}"));   // bust the ?v=mtime photo cache
$p->photo = $dstRel;
$p->save();

echo "{$p->name}  [{$p->slug}]\n";
echo "  photo set -> {$dstRel}  (AP photo outside the Harrisburg courthouse, May 25, 1971)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
