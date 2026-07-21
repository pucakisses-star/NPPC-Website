#!/usr/bin/env bash
#
# Attach portraits for the last of the photo-less "1800s"-era prisoners
# (the tail of the list, indices 150+). Every image was visually verified
# as the correct person and is public domain:
#
#   clement-c-clay-jr  — McClees 35th-Congress portrait, signed "C.C. Clay Jr. Ala."
#   david-levy-yulee   — Brady-Handy photo (first Jewish U.S. Senator, FL)
#   stephen-r-mallory  — Brady-Handy photo (Confederate Secretary of the Navy)
#
# Not attached: Father John A. Cummings (Cummings v. Missouri — no known
# likeness) and the obscure Civil War civilian detainees William S. Pickett,
# William T. Aud, and D. D. Foley (no surviving portrait). This completes the
# "1800s"-era photo-less sweep.
#
# Idempotent; sets photo only when empty.
#   bash database/data/attach-1800s-batch7.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/audit-photos"
DST="storage/app/public/prisoners"
mkdir -p "$DST"

SLUGS="clement-c-clay-jr david-levy-yulee stephen-r-mallory"

for slug in $SLUGS; do
    if [ -f "$SRC/$slug.jpg" ] && [ ! -f "$DST/$slug.jpg" ]; then
        cp "$SRC/$slug.jpg" "$DST/$slug.jpg"
        echo "copied $slug.jpg"
    fi
done

php artisan tinker --execute='
foreach (["clement-c-clay-jr","david-levy-yulee","stephen-r-mallory"] as $slug) {
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
echo "Done. 3 more verified 1800s-era portraits attached."
