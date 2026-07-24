#!/usr/bin/env bash
#
# Re-crop William Buwalda's portrait: the stored image was the full-length
# newspaper figure; this tightens it to a head-and-shoulders view (hat, face,
# and the bundle he is carrying), matching the reference framing. The photo
# field already points at prisoners/william-buwalda.jpg, so this just overwrites
# the stored file with the cropped version.
#
# Idempotent. Run from the repo root:
#   bash database/data/recrop-william-buwalda-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/photos/william-buwalda.jpg"
DST="storage/app/public/prisoners/william-buwalda.jpg"

if [ ! -f "$SRC" ]; then echo "Source $SRC missing" >&2; exit 1; fi
mkdir -p "$(dirname "$DST")"
cp -f "$SRC" "$DST"
echo "Overwrote $DST with the re-cropped portrait."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug","william-buwalda")->first();
if ($p && empty($p->photo)) { $p->photo = "prisoners/william-buwalda.jpg"; $p->save(); echo "Set photo path on {$p->name}.\n"; }
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Cache cleared.\n";
'

echo
echo "Done. William Buwalda photo re-cropped."
