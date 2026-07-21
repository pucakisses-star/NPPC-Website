#!/usr/bin/env bash
#
# Attach portraits for 11 of the first 25 photo-less "1800s"-era prisoners.
# Every image was visually verified as the correct person and is public
# domain (period photographs / engravings from Wikimedia, NPS, the American
# Antiquarian Society, NYPL-adjacent and labeled historical sources).
#
# Solo portraits:
#   black-jim, schonchin-john  — Louis Heller 1873 Modoc prisoner portraits
#   edson-b-olds               — Wikimedia congressional-era engraving
#   dennis-a-mahony            — Encyclopedia Dubuque period photo
#   francis-key-howard         — Civil War-era photograph
#   charles-g-davis            — National Park Service portrait
#
# Cropped from labeled group/family photos (only surviving likeness):
#   christopher-columbus-jones — left figure, 1894 Coxey's Army jail-steps photo
#   martin-j-elliott           — center-standing, 1894 ARU officers group photo
#   sylvester-keliher          — right-standing, same ARU group photo
#   roy-m-goodwin              — seated (right of Debs), same ARU group photo
#   edward-oconnor             — adult male, Anti-Rent-era family photograph
#
# Not found (left as-is): Van Steenburgh, Moses Earle, Martin Stowell,
# Joseph Scarlett, the 1822 Denmark Vesey conspirators (Gullah Jack, Monday
# Gell, Peter Poyas, Rolla Bennett — predate photography), the Blackburns
# (no known likeness), Alexander H. Jones, Hugh Dempsey, Barncho, and
# Ross Winans (the only located "Winans" image was a wrong-person sketch).
#
# Idempotent; sets photo only when empty.
#   bash database/data/attach-1800s-batch.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/audit-photos"
DST="storage/app/public/prisoners"
mkdir -p "$DST"

SLUGS="black-jim schonchin-john edson-b-olds dennis-a-mahony francis-key-howard charles-g-davis christopher-columbus-jones martin-j-elliott sylvester-keliher roy-m-goodwin edward-oconnor"

for slug in $SLUGS; do
    if [ -f "$SRC/$slug.jpg" ] && [ ! -f "$DST/$slug.jpg" ]; then
        cp "$SRC/$slug.jpg" "$DST/$slug.jpg"
        echo "copied $slug.jpg"
    fi
done

php artisan tinker --execute='
foreach (["black-jim","schonchin-john","edson-b-olds","dennis-a-mahony","francis-key-howard","charles-g-davis","christopher-columbus-jones","martin-j-elliott","sylvester-keliher","roy-m-goodwin","edward-oconnor"] as $slug) {
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
echo "Done. 11 verified 1800s-era portraits attached."
