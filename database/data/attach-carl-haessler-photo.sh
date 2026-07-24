#!/usr/bin/env bash
#
# Attach a portrait of Carl H. Haessler, cropped from the University of Wisconsin
# "Socialist Club" group photo (Badger yearbook, ~1911-1912; he is listed and
# captioned as "Haessler" in the front row). Public domain (pre-1928 yearbook),
# via the UW-Madison Libraries digital collection
# (search.library.wisc.edu/digital/AOS63RU5SBJFG48V, image asset
# 1711.dl/U4VBCQTJ7ENRX8N). His face was cropped to a head-and-shoulders portrait.
#
# Idempotent. Run from the repo root:
#   bash database/data/attach-carl-haessler-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/photos/carl-haessler.jpg"
DST="storage/app/public/prisoners/carl-haessler.jpg"
mkdir -p "$(dirname "$DST")"
if [ -f "$SRC" ]; then cp -f "$SRC" "$DST"; echo "copied carl-haessler.jpg"; fi

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()
    ->where("slug", "carl-haessler")
    ->orWhereRaw("LOWER(name) IN (?,?)", ["carl haessler","carl h. haessler"])
    ->first();

if (! $p) { echo "Carl Haessler not found.\n"; return; }
if (is_file(storage_path("app/public/prisoners/carl-haessler.jpg"))) {
    $p->photo = "prisoners/carl-haessler.jpg";
    $p->save();
    echo "Set photo on {$p->name} (slug: {$p->slug}).\n";
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Carl Haessler portrait attached."
