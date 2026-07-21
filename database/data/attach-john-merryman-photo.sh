#!/usr/bin/env bash
#
# Attach a portrait for John Merryman (of the 1861 habeas corpus case
# Ex parte Merryman). No period photograph survives; this is the c.1910-1920
# oil portrait by Meredith Janvier, the recognized (public-domain) likeness,
# added per the site owner's request.
#
# Idempotent. Run from the repo root:
#   bash database/data/attach-john-merryman-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/audit-photos/john-merryman.jpg"
DST="storage/app/public/prisoners/john-merryman.jpg"
mkdir -p "$(dirname "$DST")"

if [ -f "$SRC" ] && [ ! -f "$DST" ]; then
    cp "$SRC" "$DST"
    echo "copied john-merryman.jpg"
fi

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "john-merryman")->first();
if ($p && empty($p->photo) && is_file(storage_path("app/public/prisoners/john-merryman.jpg"))) {
    $p->photo = "prisoners/john-merryman.jpg";
    $p->save();
    echo "SET photo on john-merryman\n";
} else {
    echo "Nothing to do (missing, already has photo, or file absent).\n";
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. John Merryman portrait attached."
