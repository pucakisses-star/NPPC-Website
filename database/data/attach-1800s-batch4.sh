#!/usr/bin/env bash
#
# Attach portraits for 8 of the fourth 25 photo-less "1800s"-era prisoners
# (indices 75-99). Every image was visually verified as the correct person
# and is public domain:
#
#   edwin-coppoc          — photo (John Brown's Harpers Ferry raider)
#   elmina-slenker        — photo (freethought writer, Comstock-Act case)
#   edward-a-pollard      — signed engraving (Richmond Examiner editor)
#   george-eustis-jr      — engraving (Slidell's secretary, Trent Affair)
#   george-p-kane         — photo (Baltimore Marshal of Police)
#   george-q-cannon       — Brady-Handy photo (LDS apostle, anti-polygamy)
#   george-reynolds       — photo (Reynolds v. United States, 1878)
#   george-william-brown  — albumen photo (Mayor of Baltimore 1860-61)
#
# Not attached: the many obscure Fort Delaware / Baltimore Civil War civilian
# detainees with no surviving likeness (Bentley, Devitt, Stevenson, Worrell,
# Galleher, Richardson, Price, Grady, Appleton, Butler, Harris, Thorn,
# Flanagan, Dougherty); Chief Leschi (no lifetime likeness — only posthumous
# imaginative art); David Fry (real photos exist but not openly/PD-hosted);
# and Christopher Haun (only a narrative execution-scene engraving, not a
# usable portrait).
#
# Idempotent; sets photo only when empty.
#   bash database/data/attach-1800s-batch4.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/audit-photos"
DST="storage/app/public/prisoners"
mkdir -p "$DST"

SLUGS="edwin-coppoc elmina-slenker edward-a-pollard george-eustis-jr george-p-kane george-q-cannon george-reynolds george-william-brown"

for slug in $SLUGS; do
    if [ -f "$SRC/$slug.jpg" ] && [ ! -f "$DST/$slug.jpg" ]; then
        cp "$SRC/$slug.jpg" "$DST/$slug.jpg"
        echo "copied $slug.jpg"
    fi
done

php artisan tinker --execute='
foreach (["edwin-coppoc","elmina-slenker","edward-a-pollard","george-eustis-jr","george-p-kane","george-q-cannon","george-reynolds","george-william-brown"] as $slug) {
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
