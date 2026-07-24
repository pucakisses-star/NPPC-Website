#!/usr/bin/env bash
#
# Update William Weinstone: drop the "W." from the display name (William W.
# Weinstone -> William Weinstone), set the middle name to Wolf, clear the aka,
# replace the biography, and overwrite his portrait with a version cropped 15%
# off the top (trimming the bookshelf headroom on the 1968 Wikimedia photo).
#
# Idempotent. Run from the repo root:
#   bash database/data/update-william-weinstone.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/photos/william-weinstone.jpg"
DST="storage/app/public/prisoners/william-weinstone.jpg"
mkdir -p "$(dirname "$DST")"
if [ -f "$SRC" ]; then cp -f "$SRC" "$DST"; echo "copied cropped william-weinstone.jpg"; fi

php artisan tinker --execute='
$p = null;
foreach (["william-w-weinstone","william-weinstone"] as $s) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $s)->first();
    if ($p) break;
}
if (! $p) { $p = \App\Models\Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) LIKE ?", ["william%weinstone"])->first(); }
if (! $p) { echo "William Weinstone not found.\n"; return; }

$p->name = "William Weinstone";
$p->first_name = "William";
$p->middle_name = "Wolf";
$p->last_name = "Weinstone";
$p->aka = null;
$p->description = "William Weinstone was a writer, labor organizer, editor, and founding member of the Communist Party USA. He organized garment, subway, textile, and automobile workers, edited the Daily Worker, and held leadership positions in the New York and Michigan branches of the party. Indicted in June 1951 under the Smith Act, he was convicted with twelve other Communist leaders in January 1953 and sentenced to two years in federal prison. After his release he remained active in the Communist Party for the remainder of his life.";
if (is_file(storage_path("app/public/prisoners/william-weinstone.jpg"))) {
    $p->photo = "prisoners/william-weinstone.jpg";
}
$p->save();

echo "Updated {$p->name} (slug: {$p->slug}); first/middle/last = {$p->first_name}/{$p->middle_name}/{$p->last_name}; photo = {$p->photo}\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. William Weinstone updated."
