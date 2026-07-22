#!/usr/bin/env bash
#
# Attach Kwame "Beans" Shakur's photo, inmate number and mailing address.
#
# Kwame Shakur (#149677) is the co-founder/chairman of the New Afrikan
# Liberation Collective and National Director of Prison Lives Matter, serving
# a 110-year sentence in Indiana (Miami Correctional Facility / Wabash Valley).
# Photo and mailing details are from his Jericho Movement profile:
#   https://www.thejerichomovement.com/profile/kwame-shakur
# The photo is committed to the repo at
#   database/data/audit-photos/kwame-shakur.jpg
#
# (Note: this is NOT the released Indianapolis organizer of the same name who
# died in December 2025 — that is a different person. This Kwame Beans Shakur
# is alive and still incarcerated.)
#
# Idempotent: sets each field only if it is currently empty. Run from the
# repo root:
#   bash database/data/attach-kwame-shakur-info.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/audit-photos/kwame-shakur.jpg"
DST="storage/app/public/prisoners/kwame-shakur.jpg"
mkdir -p "$(dirname "$DST")"
cp -f "$SRC" "$DST"
echo "Installed $DST."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "kwame-shakur")->first();
if (! $p) { echo "kwame-shakur not found\n"; return; }

if (empty($p->photo) && is_file(storage_path("app/public/prisoners/kwame-shakur.jpg"))) {
    $p->photo = "prisoners/kwame-shakur.jpg";
    echo "SET photo\n";
}
if (empty($p->inmate_number)) {
    $p->inmate_number = "149677";
    echo "SET inmate_number\n";
}
if (empty($p->address)) {
    $p->address = "Kwame Shakur #149677, Miami Correctional Facility, 3038 West 850 South, Bunker Hill, IN 46914";
    echo "SET address\n";
}
if ($p->isDirty()) { $p->save(); echo "saved.\n"; } else { echo "nothing to do (already set).\n"; }

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Kwame Shakur photo and mailing info attached."
