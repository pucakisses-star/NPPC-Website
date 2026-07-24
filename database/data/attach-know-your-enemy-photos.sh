#!/usr/bin/env bash
#
# Attach portraits cropped from the "Know Your Enemy" anti-Communist pamphlet
# page (a 4x4 grid of 16 labeled CPUSA / Smith Act figures; Wikimedia Commons
# file Know_Your_Enemy.jpg, public domain). Only the five whose site records
# had no photo are wired up here; the other eleven already have portraits
# (see archive-photos.json). The fill-if-empty guard below is the final check —
# no existing photo is ever overwritten.
#
#   simon-gerson.jpg      Simon W. Gerson   (row 1)
#   isidore-begun.jpg     Isidore Begun     (row 2)  [pamphlet spells "Isadore"]
#   jacob-mindel.jpg      Jacob Mindel      (row 2)
#   fred-fine.jpg         Fred Fine         (row 4)  [NOT David Fine]
#   sidney-steinberg.jpg  Sidney Steinberg  (row 4)  [the Twain Harte defendant]
#
# Each record is matched by slug (several candidates) then exact name, and the
# photo is set ONLY when the record currently has none. Idempotent. Run from the
# repo root:
#   bash database/data/attach-know-your-enemy-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/photos"
DST="storage/app/public/prisoners"
mkdir -p "$DST"
for f in simon-gerson.jpg isidore-begun.jpg jacob-mindel.jpg fred-fine.jpg sidney-steinberg.jpg; do
    if [ -f "$SRC/$f" ] && [ ! -f "$DST/$f" ]; then cp "$SRC/$f" "$DST/$f"; echo "copied $f"; fi
done

php artisan tinker --execute='
// file => [ [slug candidates], [exact-name candidates] ]
$entries = [
    "simon-gerson.jpg" => [["simon-w-gerson", "simon-gerson"], ["Simon W. Gerson", "Simon Gerson"]],
    "isidore-begun.jpg" => [["isidore-begun", "isadore-begun"], ["Isidore Begun", "Isadore Begun"]],
    "jacob-mindel.jpg" => [["jacob-mindel"], ["Jacob Mindel"]],
    "fred-fine.jpg" => [["fred-fine", "fred-m-fine", "frederick-fine"], ["Fred Fine", "Fred M. Fine", "Frederick Fine"]],
    "sidney-steinberg.jpg" => [["sidney-steinberg"], ["Sidney Steinberg"]],
];

$linked = 0; $skipped = 0; $missing = [];
foreach ($entries as $file => [$slugs, $names]) {
    $p = null;
    foreach ($slugs as $s) { $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $s)->first(); if ($p) break; }
    if (! $p) { foreach ($names as $n) { $p = \App\Models\Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", [mb_strtolower($n)])->first(); if ($p) break; } }

    if (! $p) { $missing[] = $names[0]; continue; }
    if (! empty($p->photo)) { echo "{$p->name} already has a photo — leaving alone.\n"; $skipped++; continue; }
    if (! is_file(storage_path("app/public/prisoners/{$file}"))) { echo "Image missing for {$p->name}: {$file}\n"; continue; }

    $p->photo = "prisoners/{$file}";
    $p->save();
    echo "Linked {$file} -> {$p->name} (slug: {$p->slug}).\n";
    $linked++;
}

if ($linked > 0) { \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey()); }
echo "\nDone. Linked={$linked}, already-had-photo={$skipped}.\n";
if ($missing) { echo "Not found: ".implode(", ", $missing)." — pass me the exact site slug and I will map it.\n"; }
'

echo
echo "Done. Know Your Enemy portraits attached (fill-if-empty)."
