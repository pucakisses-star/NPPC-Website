#!/usr/bin/env bash
#
# Attach portraits for 9 of the next 25 photo-less "1800s"-era prisoners
# (the second batch). Every image was visually verified as the correct
# person and is public domain:
#
#   black-hawk              — George Catlin 1832 from-life portrait (Smithsonian)
#   william-lloyd-garrison  — Library of Congress bust engraving (signed plate)
#   cyrus-pringle           — studio portrait (Pringle Herbarium, U. Vermont)
#   pryce-lewis             — period photo (St. Lawrence Univ. collection)
#   timothy-webster         — Harper's engraving of the Pinkerton spy
#   john-kehoe              — 1870s Bretz photo of the Molly Maguire "King"
#   thomas-duffy            — labeled Molly Maguire engraving (Famous Trials)
#   thomas-munley           — labeled Molly Maguire engraving (Famous Trials)
#   tilghman-vestal         — 1866 CDV (Friends Historical Collection, Guilford)
#
# Not attached: the other five Molly Maguires (Bergen, Hester, Tully, McHugh,
# Fisher — no individually-labeled portrait), the 1815 New Orleans figures
# (John Dick, Louis Louaillier — predate photography), the San Patricios
# (1847, no authentic likeness — John Riley has only modern statues), and the
# Quaker COs Himelius/Jesse Hockett, Peter Dakin, Seth Laughlin (no accessible
# image). Judge Dominick A. Hall was deliberately skipped: the only image is a
# posthumous 1859 history-painting figure, not an authentic likeness.
#
# Idempotent; sets photo only when empty.
#   bash database/data/attach-1800s-batch2.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/audit-photos"
DST="storage/app/public/prisoners"
mkdir -p "$DST"

SLUGS="black-hawk william-lloyd-garrison cyrus-pringle pryce-lewis timothy-webster john-kehoe thomas-duffy thomas-munley tilghman-vestal"

for slug in $SLUGS; do
    if [ -f "$SRC/$slug.jpg" ] && [ ! -f "$DST/$slug.jpg" ]; then
        cp "$SRC/$slug.jpg" "$DST/$slug.jpg"
        echo "copied $slug.jpg"
    fi
done

php artisan tinker --execute='
foreach (["black-hawk","william-lloyd-garrison","cyrus-pringle","pryce-lewis","timothy-webster","john-kehoe","thomas-duffy","thomas-munley","tilghman-vestal"] as $slug) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if ($p && empty($p->photo) && is_file(storage_path("app/public/prisoners/{$slug}.jpg"))) {
        $p->photo = "prisoners/{$slug}.jpg";
        $p->save();
        echo "SET photo on {$slug}\n";
    } else {
        echo "skip {$slug} (missing, already has photo, or file absent)\n";
    }
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. 9 more verified 1800s-era portraits attached."
