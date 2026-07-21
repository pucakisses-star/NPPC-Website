#!/usr/bin/env bash
#
# Replace William Lloyd Garrison's portrait with the iconic late-life
# photograph (bald, wire-rim glasses) supplied by the site owner, in place
# of the earlier young-Garrison engraving. Public domain (Garrison d. 1879).
#
# Overwrites the stored file and (re)points the photo field at it.
# Idempotent. Run from the repo root:
#   bash database/data/update-garrison-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/audit-photos/william-lloyd-garrison.jpg"
DST="storage/app/public/prisoners/william-lloyd-garrison.jpg"
mkdir -p "$(dirname "$DST")"
cp -f "$SRC" "$DST"
echo "Overwrote $DST with the Garrison photograph."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "william-lloyd-garrison")->first();
if ($p) {
    $p->photo = "prisoners/william-lloyd-garrison.jpg";
    $p->save();
    echo "SET photo on william-lloyd-garrison\n";
} else {
    echo "william-lloyd-garrison not found\n";
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. William Lloyd Garrison photo updated."
