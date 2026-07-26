#!/usr/bin/env bash
#
# Attach head-and-shoulders portraits of Ailene Holmes and Mabel Husa, cropped
# from the contemporary press photograph of the two behind bars after the 1930
# Van Etten flag case (Labor Defender, vol. 5 no. 9, Sept 1930). In the source
# image Holmes is on the RIGHT and Husa on the LEFT; each was cropped to an
# individual portrait.
#
# Idempotent. Run from the repo root:
#   bash database/data/attach-holmes-husa-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

for slug in ailene-holmes mabel-husa; do
    SRC="database/data/photos/${slug}.jpg"
    DST="storage/app/public/prisoners/${slug}.jpg"
    mkdir -p "$(dirname "$DST")"
    if [ -f "$SRC" ]; then cp -f "$SRC" "$DST"; echo "copied ${slug}.jpg"; fi
done

php artisan tinker --execute='
foreach (["ailene-holmes" => "Ailene Holmes", "mabel-husa" => "Mabel Husa"] as $slug => $name) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()
        ->where("slug", $slug)
        ->orWhereRaw("LOWER(name) = ?", [strtolower($name)])
        ->first();
    if (! $p) { echo "{$name} not found (skipped).\n"; continue; }
    if (is_file(storage_path("app/public/prisoners/{$slug}.jpg"))) {
        $p->photo = "prisoners/{$slug}.jpg";
        $p->save();
        echo "Set photo on {$p->name} (slug: {$p->slug}).\n";
    }
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Holmes and Husa portraits attached."
