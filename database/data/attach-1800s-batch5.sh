#!/usr/bin/env bash
#
# Attach portraits for 8 of the fifth 25 photo-less "1800s"-era prisoners
# (indices 100-124). Every image was visually verified as the correct person
# and is public domain:
#
#   john-brown                — Augustus Washington c.1847 daguerreotype (the abolitionist)
#   john-e-cook               — engraving (Harpers Ferry raider / Brown's scout)
#   john-h-reagan             — Brady-Handy photo (Confederate Postmaster General)
#   jefferson-davis           — Brady photo (Confederate president)
#   james-g-berrett           — photo (Mayor of Washington, D.C.)
#   james-w-wall              — Congressional bioguide portrait (NJ senator)
#   john-taylor-lds-president — photo (3rd LDS Church President)
#   lambdin-p-milligan        — engraving (Ex parte Milligan, 1866)
#
# Not attached: the many obscure Fort Delaware / Maryland-Virginia Civil War
# civilian detainees with no surviving likeness; Henry May (Congressman, but
# no portrait located); John Merryman (only a posthumous ~1915 painting, not
# a period likeness); and the East Tennessee bridge-burners Jacob Harmon /
# Jacob Hinshaw (only narrative execution-scene engravings).
#
# Idempotent; sets photo only when empty.
#   bash database/data/attach-1800s-batch5.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/audit-photos"
DST="storage/app/public/prisoners"
mkdir -p "$DST"

SLUGS="john-brown john-e-cook john-h-reagan jefferson-davis james-g-berrett james-w-wall john-taylor-lds-president lambdin-p-milligan"

for slug in $SLUGS; do
    if [ -f "$SRC/$slug.jpg" ] && [ ! -f "$DST/$slug.jpg" ]; then
        cp "$SRC/$slug.jpg" "$DST/$slug.jpg"
        echo "copied $slug.jpg"
    fi
done

php artisan tinker --execute='
foreach (["john-brown","john-e-cook","john-h-reagan","jefferson-davis","james-g-berrett","james-w-wall","john-taylor-lds-president","lambdin-p-milligan"] as $slug) {
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
echo "Done. 8 more verified 1800s-era portraits attached."
