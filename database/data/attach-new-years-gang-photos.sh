#!/usr/bin/env bash
#
# Attach portraits for the four New Years Gang / Sterling Hall bombing (UW-Madison,
# August 24, 1970) defendants. All four images are public domain: the FBI wanted
# posters issued after the bombing (a work of the U.S. federal government), plus
# Leo Burt's separate 1969 ID photo. Sourced from Wikimedia Commons; see
# CREDITS-wikipedia.md.
#
#   leo-frederick-burt.jpg   Burt's 1969 photo (Commons: Leo_Frederick_Burt_1969.jpg)
#   david-fine.jpg           cropped from the FBI wanted-poster composite
#   karleton-armstrong.jpg   the 1970 (bearded) wanted-poster photo, from the composite
#   dwight-armstrong.jpg     cropped from the FBI wanted-poster composite
#
# (composite Commons file: Sterling_Hall_Bombers.jpg)
#
# Each prisoner is matched by slug (several candidates) then by exact name, and
# the photo is set ONLY when the record currently has none. Idempotent; safe to
# re-run. Run from the repo root:
#   bash database/data/attach-new-years-gang-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/photos"
DST="storage/app/public/prisoners"
mkdir -p "$DST"
for f in leo-frederick-burt.jpg david-fine.jpg karleton-armstrong.jpg dwight-armstrong.jpg; do
    if [ -f "$SRC/$f" ] && [ ! -f "$DST/$f" ]; then cp "$SRC/$f" "$DST/$f"; echo "copied $f"; fi
done

php artisan tinker --execute='
// file => [ [slug candidates], [name candidates] ]
$entries = [
    "leo-frederick-burt.jpg" => [["leo-frederick-burt", "leo-burt"], ["Leo Frederick Burt", "Leo Burt"]],
    "david-fine.jpg" => [["david-fine", "david-sylvan-fine"], ["David Fine", "David Sylvan Fine"]],
    "karleton-armstrong.jpg" => [["karleton-armstrong", "karleton-lewis-armstrong", "karl-armstrong"], ["Karleton Armstrong", "Karleton Lewis Armstrong", "Karl Armstrong"]],
    "dwight-armstrong.jpg" => [["dwight-armstrong", "dwight-alan-armstrong", "dwight-allan-armstrong"], ["Dwight Armstrong", "Dwight Alan Armstrong", "Dwight Allan Armstrong"]],
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
echo "Done. New Years Gang portraits attached (fill-if-empty)."
