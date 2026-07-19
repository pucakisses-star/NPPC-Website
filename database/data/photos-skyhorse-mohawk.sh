#!/usr/bin/env bash
#
# Attaches three AIM-era portraits:
#  - Paul Skyhorse and Richard Mohawk, cropped from the individually
#    captioned photos in the Native American Solidarity Committee's
#    "Free Paul Skyhorse and Richard Mohawk" defense pamphlet (Freedom
#    Archives DOC44 scan) — the pamphlet itself is registered in the
#    site archive.
#  - Richard Oakes: the Stephen Shames portrait from the Alcatraz
#    occupation, supplied by the site owner.
#
# Fill-if-empty; idempotent.
#
# Run from the repo root:  bash database/data/photos-skyhorse-mohawk.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
\Illuminate\Support\Facades\Storage::disk("public")->makeDirectory("prisoners");
$photos = [
    "paul-skyhorse" => "nonfree/paul-skyhorse.jpg",
    "richard-mohawk" => "nonfree/richard-mohawk.jpg",
    "richard-oakes" => "nonfree/richard-oakes.jpg",
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

php artisan archive:add-skyhorse-mohawk-pamphlet

echo
echo "Done. AIM-era portraits applied."
