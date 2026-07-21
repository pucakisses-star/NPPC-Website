#!/usr/bin/env bash
#
# Attach portraits for 12 of the third 25 photo-less "1800s"-era prisoners.
# Every image was visually verified as the correct person and is public
# domain (period photographs, daguerreotypes/ambrotypes, and labeled
# engravings from Wikimedia, the Library of Congress, William Still's
# "Underground Railroad", and named period books):
#
#   john-hossack        — photo (Ottawa IL UGRR merchant)
#   jonathan-walker     — 1848 engraving (the "branded hand" sea captain)
#   samuel-d-burris     — c.1850 portrait (Delaware UGRR conductor)
#   thomas-garrett      — c.1850 ambrotype (Wilmington Quaker stationmaster)
#   leonard-grimes      — 1860s LoC carte-de-visite (Boston minister)
#   william-chaplin     — c.1851 daguerreotype
#   samuel-green        — engraving from Still's "Underground Railroad"
#   paul-corcoran       — 1900 photo (Burke Miners' Union / WFM)
#   alexander-manly     — photo (Wilmington Daily Record editor)
#   aaron-stevens       — labeled line portrait (John Brown's lieutenant)
#   amos-dresser        — engraving (whipped Nashville 1835)
#   andrew-humphreys    — engraving (Indiana Sons of Liberty defendant)
#
# Not attached (no authentic likeness / unretrievable): John Gill Craven,
# Luther Donnell, Mark Caesar, Oswell Wright, Richard Eells, Robert Fee,
# Simeon Bushnell (only an unlabeled group photo), William Baylis, William
# Wheeler, Frank Manly (only an unidentified joint photo, rights-restricted),
# Alfred Campbell Belt, B. H. McCown, and John Hunn (only a painted portrait,
# blocked behind the state site's firewall).
#
# Idempotent; sets photo only when empty.
#   bash database/data/attach-1800s-batch3.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/audit-photos"
DST="storage/app/public/prisoners"
mkdir -p "$DST"

SLUGS="john-hossack jonathan-walker samuel-d-burris thomas-garrett leonard-grimes william-chaplin samuel-green paul-corcoran alexander-manly aaron-stevens amos-dresser andrew-humphreys"

for slug in $SLUGS; do
    if [ -f "$SRC/$slug.jpg" ] && [ ! -f "$DST/$slug.jpg" ]; then
        cp "$SRC/$slug.jpg" "$DST/$slug.jpg"
        echo "copied $slug.jpg"
    fi
done

php artisan tinker --execute='
foreach (["john-hossack","jonathan-walker","samuel-d-burris","thomas-garrett","leonard-grimes","william-chaplin","samuel-green","paul-corcoran","alexander-manly","aaron-stevens","amos-dresser","andrew-humphreys"] as $slug) {
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
echo "Done. 12 more verified 1800s-era portraits attached."
