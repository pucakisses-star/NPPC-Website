#!/usr/bin/env bash
#
# Attach portraits for Anonymous/LulzSec prisoners that were missing photos.
# Only people with a verified, openly-accessible photo of the correct person
# are included:
#   - Hector Xavier Monsegur "Sabu" — clean speaker-headshot (500x500)
#   - Ross Colby — staff news photo from The Almanac trial coverage
#   - Cody Andrew Kretsinger "recursion" — Arizona TV-news still (low-res,
#     near-profile, but verified the correct person; replaceable later)
#
# No usable openly-accessible portrait was found for Raynaldo Rivera,
# Brian Thomas Mettenbrink, or James E. Robinson, so they are left as-is.
#
# The files ship in database/data/audit-photos/; this copies each into
# storage and sets the photo field only when empty. Idempotent.
#   bash database/data/attach-anonymous-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/audit-photos"
DST="storage/app/public/prisoners"
mkdir -p "$DST"

for slug in hector-xavier-monsegur ross-colby cody-andrew-kretsinger; do
    if [ -f "$SRC/$slug.jpg" ] && [ ! -f "$DST/$slug.jpg" ]; then
        cp "$SRC/$slug.jpg" "$DST/$slug.jpg"
        echo "copied $slug.jpg"
    fi
done

php artisan tinker --execute='
foreach (["hector-xavier-monsegur", "ross-colby", "cody-andrew-kretsinger"] as $slug) {
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
echo "Done. Anonymous prisoner photos attached."
