#!/usr/bin/env bash
#
# Read-only photo audit: counts files in storage/app/public/prisoners
# that no prisoner record references (orphans), and prisoner records
# whose photo path points at a missing file (broken). Changes nothing.
#
# Run from the repo root:  bash database/data/photos-audit.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$photos = \App\Models\Prisoner::withoutGlobalScopes()
    ->whereNotNull("photo")->where("photo", "!=", "")
    ->pluck("photo", "slug");
$usedBasenames = $photos->map(fn ($p) => basename($p))->flip();
$files = collect(\Illuminate\Support\Facades\Storage::disk("public")->files("prisoners"));

$orphans = $files->reject(fn ($f) => isset($usedBasenames[basename($f)]))->values();
$broken = $photos->reject(fn ($p) => \Illuminate\Support\Facades\Storage::disk("public")->exists($p));

echo "Files in storage prisoners/:        " . $files->count() . "\n";
echo "Prisoner records with a photo set:  " . $photos->count() . "\n";
echo "ORPHANED files (no record uses):    " . $orphans->count() . "\n";
echo "BROKEN records (file missing):      " . $broken->count() . "\n\n";
if ($orphans->isNotEmpty()) {
    echo "First 40 orphans:\n";
    foreach ($orphans->take(40) as $f) { echo "  " . $f . "\n"; }
}
if ($broken->isNotEmpty()) {
    echo "\nBroken records:\n";
    foreach ($broken->take(40) as $slug => $p) { echo "  {$slug} -> {$p}\n"; }
}
echo "\nDone (read-only).\n";
'
