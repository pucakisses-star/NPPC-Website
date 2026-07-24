#!/usr/bin/env bash
#
# Attach portraits for the September 1901 Chicago "Free Society" roundup
# arrestees, cropped from the "MENACE OF ANARCHY" newspaper montage of the
# anarchists taken from 515 Carroll Avenue (a 1901 Chicago newspaper page,
# public domain). Source scan:
#   thetransmetropolitanreview.wordpress.com/.../screenshot-2024-05-26-...png
#
# Ten labeled sketches are wired up (the two Isaaks already have photos from
# the Jane Addams Digital Edition and are left alone):
#   alfred-schneider, enrico-travaglio (labeled Henry Travaglio),
#   clemens-pfuetzner (Clement), mary-isaak (labeled Mrs Isaak, the wife),
#   jay-fox (labeled Morris J. Fox), julia-mechanic, michael-roz (Roze),
#   hippolyte-havel, marie-isaak (labeled Miss Mary Isaak, the daughter),
#   martin-rasnick (Raznick).
#
# Each record is matched by slug then exact name; the photo is set ONLY when the
# record currently has none. Idempotent. Run from the repo root:
#   bash database/data/attach-free-society-menace-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/photos"
DST="storage/app/public/prisoners"
mkdir -p "$DST"
for f in alfred-schneider enrico-travaglio clemens-pfuetzner mary-isaak jay-fox julia-mechanic michael-roz hippolyte-havel marie-isaak martin-rasnick; do
    if [ -f "$SRC/$f.jpg" ] && [ ! -f "$DST/$f.jpg" ]; then cp "$SRC/$f.jpg" "$DST/$f.jpg"; echo "copied $f.jpg"; fi
done

php artisan tinker --execute='
$entries = [
    "alfred-schneider.jpg" => [["alfred-schneider"], ["Alfred Schneider"]],
    "enrico-travaglio.jpg" => [["enrico-travaglio"], ["Enrico Travaglio","Henry Travaglio"]],
    "clemens-pfuetzner.jpg" => [["clemens-pfuetzner"], ["Clemens Pfuetzner","Clement Pfuetzner"]],
    "mary-isaak.jpg" => [["mary-isaak"], ["Mary Isaak","Maria Isaak"]],
    "jay-fox.jpg" => [["jay-fox"], ["Jay Fox","Morris J. Fox"]],
    "julia-mechanic.jpg" => [["julia-mechanic"], ["Julia Mechanic"]],
    "michael-roz.jpg" => [["michael-roz"], ["Michael Roz","Michael Roze"]],
    "hippolyte-havel.jpg" => [["hippolyte-havel"], ["Hippolyte Havel"]],
    "marie-isaak.jpg" => [["marie-isaak"], ["Marie Isaak"]],
    "martin-rasnick.jpg" => [["martin-rasnick"], ["Martin Rasnick","Martin Raznick"]],
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
if ($missing) { echo "Not found: ".implode(", ", $missing)."\n"; }
'

echo
echo "Done. Free Society roundup portraits attached (fill-if-empty)."
