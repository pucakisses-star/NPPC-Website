#!/usr/bin/env bash
#
# Portrait for Benjamin Gwinn Harris -- the Maryland congressman convicted by a
# military commission in 1865 for aiding and harbouring Confederate soldiers,
# sentenced to imprisonment and permanent disqualification from office, with
# the prison term afterwards remitted.
#
# The image is the Brady National Photographic Art Gallery portrait of c. 1863,
# public domain, from Wikimedia Commons via his Wikipedia article. It arrives
# already framed as head and shoulders at 336x448, so it is used as downloaded
# with no crop.
#   https://commons.wikimedia.org/wiki/File:Benjamin_Gwinn_Harris_(Maryland_Congressman)_(1).jpg
#
# Only attached where the record has no photo, so an existing portrait is never
# overwritten; pass FORCE=1 to replace regardless.
#
# Idempotent. Run from the repo root:
#   bash database/data/attach-benjamin-gwinn-harris-photo.sh
#   FORCE=1 bash database/data/attach-benjamin-gwinn-harris-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

FORCE="${FORCE:-0}" php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$force = getenv("FORCE") === "1";

$matches = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->whereIn("slug", ["benjamin-gwinn-harris", "benjamin-g-harris"])
        ->orWhereRaw("LOWER(name) IN (?, ?)", ["benjamin gwinn harris", "benjamin g. harris"]))
    ->get();

if ($matches->isEmpty()) { echo "NOT FOUND: Benjamin Gwinn Harris\n"; exit(1); }
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

$src = base_path("database/data/photos/benjamin-gwinn-harris.jpg");
if (! is_file($src)) { echo "MISSING SOURCE: database/data/photos/benjamin-gwinn-harris.jpg\n"; exit(1); }

$dstRel = "prisoners/{$p->slug}.jpg";
File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
File::copy($src, storage_path("app/public/{$dstRel}"));
touch(storage_path("app/public/{$dstRel}"));   // bust the ?v=mtime photo cache
$p->photo = $dstRel;
$p->save();

echo "{$p->name}  [{$p->slug}]\n";
echo "  photo set -> {$dstRel}  (Brady portrait, c. 1863, public domain)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
