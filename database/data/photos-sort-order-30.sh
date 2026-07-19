#!/usr/bin/env bash
#
# Photo hunt over the first 30 photoless prisoners in the site's sort order
# (July 2026). Two caption-certified portraits were found and verified;
# the hunt also surfaced the Gabriela/Gabriella Oropesa duplicate pair,
# merged here.
#
#  - Arthur C. Townley: 1922 public-domain portrait (Wikimedia Commons).
#  - Alfredo "Lelo" Juarez Zeferino: cropped from the KUOW photograph whose
#    caption places him front left (photos/nonfree/, CREDITS-nonfree.md).
#
# Fill-if-empty; idempotent.
#
# Run from the repo root:  bash database/data/photos-sort-order-30.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoners:merge-duplicates --only=gabriella-oropesa --apply

php artisan tinker --execute='
\Illuminate\Support\Facades\Storage::disk("public")->makeDirectory("prisoners");
$photos = [
    "arthur-c-townley" => "arthur-c-townley.jpg",
    "alfredo-juarez-zeferino" => "nonfree/alfredo-juarez-zeferino.jpg",
];
$linked = 0;
foreach ($photos as $slug => $file) {
    $p = \App\Models\Prisoner::withUnderReview()->where("slug", $slug)->first();
    if (! $p) { echo "MISS {$slug}\n"; continue; }
    if (! empty($p->photo)) { echo "SKIP {$slug} (already has a photo)\n"; continue; }
    $src = database_path("data/photos/{$file}");
    if (! is_file($src)) { echo "NOFILE {$file}\n"; continue; }
    $relative = "prisoners/" . basename($file);
    \Illuminate\Support\Facades\Storage::disk("public")->put($relative, (string) file_get_contents($src));
    $p->photo = $relative;
    $p->save();
    $linked++;
    echo "PHOTO {$slug}\n";
}
if ($linked > 0) {
    \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
}
echo "Done. {$linked} photo(s) linked.\n";
'

echo
echo "Done. Sort-order photo batch applied."
