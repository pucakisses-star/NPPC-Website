#!/usr/bin/env bash
#
# Attach portraits found for a random sample of 50 previously photo-less
# prisoners. Only the 7 whose photo was located AND visually verified as the
# correct person (from openly-accessible sources — Wikimedia, government
# releases, news, org/self-published) are included. The other 43 had no
# findable/verifiable openly-accessible portrait (mostly obscure WWI
# conscientious objectors and 1910s-30s IWW/labor prisoners).
#
#   alden-whitman        — Wikimedia Commons (1956 NYT portrait)
#   patricia-stephens    — Wikimedia Commons (Patricia Stephens Due)
#   pedro-bissonette     — Find A Grave memorial (exact death-date match)
#   rothschild-augustine — U.S. Attorney's Office booking photo (via CBS)
#   sean-cardenas        — WIFR booking mugshot (2021 Rockford arson case)
#   peter-dougherty      — Meta Peace Team org page (his own org)
#   jim-albertini        — CovertAction / Malu 'Aina (self-published)
#
# The files ship in database/data/audit-photos/; this copies each into
# storage and sets the photo field only when empty. Idempotent.
#   bash database/data/attach-photo50-batch.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/audit-photos"
DST="storage/app/public/prisoners"
mkdir -p "$DST"

SLUGS="alden-whitman patricia-stephens pedro-bissonette rothschild-augustine sean-cardenas peter-dougherty jim-albertini"

for slug in $SLUGS; do
    if [ -f "$SRC/$slug.jpg" ] && [ ! -f "$DST/$slug.jpg" ]; then
        cp "$SRC/$slug.jpg" "$DST/$slug.jpg"
        echo "copied $slug.jpg"
    fi
done

php artisan tinker --execute='
foreach (["alden-whitman","patricia-stephens","pedro-bissonette","rothschild-augustine","sean-cardenas","peter-dougherty","jim-albertini"] as $slug) {
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
echo "Done. 7 verified portraits attached."
