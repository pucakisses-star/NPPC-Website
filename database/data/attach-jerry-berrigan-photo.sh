#!/usr/bin/env bash
#
# Attach a portrait for Jerry (Jerome) Berrigan (1919-2015), the peace
# activist and brother of Daniel and Philip Berrigan. Photo supplied by
# the site owner. The file ships in database/data/audit-photos/; the
# script copies it into storage and sets the photo field.
#
# Idempotent. Run from the repo root:
#   bash database/data/attach-jerry-berrigan-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/audit-photos"
DST="storage/app/public/prisoners"
mkdir -p "$DST"

if [ -f "$SRC/jerry-berrigan.jpg" ] && [ ! -f "$DST/jerry-berrigan.jpg" ]; then
    cp "$SRC/jerry-berrigan.jpg" "$DST/jerry-berrigan.jpg"
    echo "copied jerry-berrigan.jpg"
fi

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "jerry-berrigan")->first();
if ($p && empty($p->photo) && is_file(storage_path("app/public/prisoners/jerry-berrigan.jpg"))) {
    $p->photo = "prisoners/jerry-berrigan.jpg";
    $p->save();
    echo "SET photo on jerry-berrigan\n";
} else {
    echo "Nothing to do.\n";
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Jerry Berrigan photo attached."
