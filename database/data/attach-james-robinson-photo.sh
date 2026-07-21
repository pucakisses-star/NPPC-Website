#!/usr/bin/env bash
#
# Attach a portrait for James E. Robinson (the Akron "AkronPhoenix420"
# hacker). Photo from the Akron Beacon Journal's June 2018 coverage of his
# case, supplied by the site owner. The file ships in
# database/data/audit-photos/; this copies it into storage and sets the
# photo field.
#
# Idempotent. Run from the repo root:
#   bash database/data/attach-james-robinson-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/audit-photos/james-e-robinson.jpg"
DST="storage/app/public/prisoners/james-e-robinson.jpg"
mkdir -p "$(dirname "$DST")"

if [ -f "$SRC" ] && [ ! -f "$DST" ]; then
    cp "$SRC" "$DST"
    echo "copied james-e-robinson.jpg"
fi

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "james-e-robinson")->first();
if ($p && empty($p->photo) && is_file(storage_path("app/public/prisoners/james-e-robinson.jpg"))) {
    $p->photo = "prisoners/james-e-robinson.jpg";
    $p->save();
    echo "SET photo on james-e-robinson\n";
} else {
    echo "Nothing to do (missing, already has photo, or file absent).\n";
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. James Robinson photo attached."
