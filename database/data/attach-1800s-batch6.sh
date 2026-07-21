#!/usr/bin/env bash
#
# Attach portraits for 10 of the sixth 25 photo-less "1800s"-era prisoners
# (indices 125-149). Every image was visually verified as the correct person
# and is public domain:
#
#   susan-b-anthony            — Frances Benjamin Johnston photo, c.1900
#   prudence-crandall          — Francis Alexander from-life oil portrait, 1834
#   pierce-butler              — c.1847 daguerreotype (Weeping Time slaveholder)
#   sherman-booth              — photo (Wisconsin abolitionist, Ableman v. Booth)
#   severn-teackle-wallis      — portrait plate (Baltimore lawyer)
#   richard-bennett-carmichael — engraving (Maryland judge arrested from the bench)
#   thomas-a-r-nelson          — Brady-Handy photo (East TN Unionist congressman)
#   spencer-kellogg-brown      — Kansas Memory CDV (Union scout, hanged 1863)
#   william-a-bowles           — Indianapolis treason-trials plate (Sons of Liberty)
#   stephen-horsey             — Indianapolis treason-trials plate (Ex parte Milligan co-defendant)
#
# william-a-bowles and stephen-horsey are cropped from the labeled 1865
# "Arraigned at Indianapolis for Treason" engraved plate (each oval is
# individually captioned). Not attached: the many obscure Fort Delaware /
# Baltimore civilian detainees with no likeness; Reuben Crandall, John
# Merryman-style posthumous-only cases were skipped; thomas-cannon (repeat
# from an earlier slice) still had no image.
#
# Idempotent; sets photo only when empty.
#   bash database/data/attach-1800s-batch6.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/audit-photos"
DST="storage/app/public/prisoners"
mkdir -p "$DST"

SLUGS="susan-b-anthony prudence-crandall pierce-butler sherman-booth severn-teackle-wallis richard-bennett-carmichael thomas-a-r-nelson spencer-kellogg-brown william-a-bowles stephen-horsey"

for slug in $SLUGS; do
    if [ -f "$SRC/$slug.jpg" ] && [ ! -f "$DST/$slug.jpg" ]; then
        cp "$SRC/$slug.jpg" "$DST/$slug.jpg"
        echo "copied $slug.jpg"
    fi
done

php artisan tinker --execute='
foreach (["susan-b-anthony","prudence-crandall","pierce-butler","sherman-booth","severn-teackle-wallis","richard-bennett-carmichael","thomas-a-r-nelson","spencer-kellogg-brown","william-a-bowles","stephen-horsey"] as $slug) {
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
echo "Done. 10 more verified 1800s-era portraits attached."
