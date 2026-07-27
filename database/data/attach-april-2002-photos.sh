#!/usr/bin/env bash
#
# Portraits for four of the six April 12, 2002 federal detainees:
#
#   Scott Anderson     Vanity Fair portrait
#   Mark Dombowsky     from his own site
#   Hillary Hosta      Vanity Fair portrait
#   Pelle Pettersson   Sveriges Radio
#
# Each was cropped from a candid or editorial frame down to head and
# shoulders, since none was already a portrait.
#
# SCOTT PAUL HAS NO PHOTO: the-nature-of-music.com serves that image from
# behind a CAPTCHA wall, which returns an HTML challenge page rather than the
# JPEG. Send another source and it can be added.
#
# Photos are only attached where the record has none, so an existing portrait
# is never overwritten; pass FORCE=1 to replace regardless.
#
# Idempotent. Run from the repo root:
#   bash database/data/attach-april-2002-photos.sh
#   FORCE=1 bash database/data/attach-april-2002-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

FORCE="${FORCE:-0}" php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$force = getenv("FORCE") === "1";

$map = [
    "Scott Anderson" => "scott-anderson",
    "Mark Dombowsky" => "mark-dombowsky",
    "Hillary Hosta" => "hillary-hosta",
    "Pelle Pettersson" => "pelle-pettersson",
];

$attached = 0; $skipped = 0;
foreach ($map as $name => $file) {
    $p = Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", [strtolower($name)])->first();
    if (! $p) { echo "NOT FOUND: {$name}\n"; continue; }

    $src = base_path("database/data/photos/april-2002/{$file}.jpg");
    if (! is_file($src)) { echo "MISSING SOURCE: {$file}.jpg\n"; continue; }

    if ($p->photo && ! $force) {
        echo "{$p->name}: already has a photo ({$p->photo}) -- left alone, FORCE=1 to replace\n";
        $skipped++;
        continue;
    }

    $dstRel = "prisoners/{$p->slug}.jpg";
    File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
    File::copy($src, storage_path("app/public/{$dstRel}"));
    $p->photo = $dstRel;
    $p->save();
    echo "{$p->name}: photo set -> {$dstRel}\n";
    $attached++;
}

echo "\nScott Paul: no photo -- the source host serves it behind a CAPTCHA.\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done. Attached {$attached}, skipped {$skipped}.\n";
'

echo
echo "Done."
