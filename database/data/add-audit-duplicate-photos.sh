#!/usr/bin/env bash
#
# From the NPPC missing-photo audit: fill the two records the audit
# identified as duplicates of an existing photographed record, by reusing
# a photo we already host (no third-party image involved).
#
#   frank-cordero  -> duplicate/typo of frank-cordaro (both are the Des
#                     Moines Catholic Worker Plowshares activist Frank
#                     Cordaro) -> reuse prisoners/frank-cordaro.jpg
#   stephen-kelly  -> same person as steve-kelly-sj (Jesuit Plowshares
#                     activist Steve Kelly SJ; both on the 1997 Prince of
#                     Peace Plowshares at Bath Iron Works) -> reuse
#                     prisoners/steve-kelly.jpg
#
# Each empty record gets its own copy of the file (named for its slug) so
# the storage layout stays one-file-per-record. Idempotent: skips a record
# that already has a photo, and only copies when the source exists.
#
# NOTE: the other 27 "confirmed" rows in the audit are third-party
# copyrighted images (Reuters/AP news photos, university and organization
# portraits, historical-society and newspaper archives). The audit itself
# states a confirmed identity match is NOT copyright clearance. Those are
# intentionally NOT added here.
#
# Run from the repo root:  bash database/data/add-audit-duplicate-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

PDIR="storage/app/public/prisoners"

copy_if () { # $1 = source slug, $2 = dest slug
    if [ -f "$PDIR/$1.jpg" ] && [ ! -f "$PDIR/$2.jpg" ]; then
        cp "$PDIR/$1.jpg" "$PDIR/$2.jpg"
        echo "copied $1.jpg -> $2.jpg"
    fi
}

copy_if frank-cordaro frank-cordero
copy_if steve-kelly   stephen-kelly

php artisan tinker --execute='
$set = function (string $slug, string $file) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if ($p && empty($p->photo) && is_file(storage_path("app/public/".$file))) {
        $p->photo = $file;
        $p->save();
        echo "SET photo on {$slug}\n";
    }
};
$set("frank-cordero", "prisoners/frank-cordero.jpg");
$set("stephen-kelly", "prisoners/stephen-kelly.jpg");

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Duplicate-record photos filled (Frank Cordero, Stephen Kelly)."
