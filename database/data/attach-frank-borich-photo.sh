#!/usr/bin/env bash
#
# Attach the Frank Borich portrait, cropped from the crowd photograph in
# William Z. Foster's 1932 "Deportation" pamphlet (marxists.org), where Borich
# is the suited figure at the front of the Pittsburgh miners' demonstration
# indicated by the arrow. Public domain (1932). Head-and-shoulders crop.
#
# Idempotent. Run from the repo root:
#   bash database/data/attach-frank-borich-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/photos/frank-borich.jpg"
DST="storage/app/public/prisoners/frank-borich.jpg"
mkdir -p "$(dirname "$DST")"
[ -f "$SRC" ] && cp -f "$SRC" "$DST" && echo "copied frank-borich.jpg"

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()
    ->where("slug", "frank-borich")
    ->orWhereRaw("LOWER(name) = ?", ["frank borich"])
    ->first();
if (! $p) { echo "Frank Borich not found.\n"; return; }
if (is_file(storage_path("app/public/prisoners/frank-borich.jpg"))) {
    $p->photo = "prisoners/frank-borich.jpg";
    $p->save();
    echo "Set photo on {$p->name} (slug: {$p->slug}).\n";
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
echo
echo "Done. Frank Borich portrait attached."
