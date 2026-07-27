#!/usr/bin/env bash
#
# Maria Cueto: born December 19, 1943; died June 23, 2012. Sets her portrait
# from the 1975 Episcopal News Service photograph.
#
# Idempotent (force-replaces the photo). Run from the repo root:
#   bash database/data/fix-maria-cueto.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$p = Prisoner::withoutGlobalScopes()->where("slug", "maria-cueto")->first()
    ?? Prisoner::withoutGlobalScopes()->where("name", "like", "%Cueto%")->first();
if (! $p) { echo "NOT FOUND: Maria Cueto\n"; exit(1); }

$p->setPartialDate("birthdate", 1943, 12, 19);
$p->setPartialDate("death_date", 2012, 6, 23);
$p->save();
echo "{$p->name}: born 1943-12-19, died 2012-06-23, age {$p->age}.\n";

$src = base_path("database/data/photos/maria-cueto.jpg");
$dstRel = "prisoners/{$p->slug}.jpg";
if (is_file($src)) {
    File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
    File::copy($src, storage_path("app/public/{$dstRel}"));
    $p->photo = $dstRel;
    $p->save();
    echo "  photo set -> {$dstRel}\n";
} else {
    echo "  PHOTO SOURCE MISSING: database/data/photos/maria-cueto.jpg\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
