#!/usr/bin/env bash
#
# Photo hunt over the first 50 photoless 2000s-era prisoners (July 2026)
# — ALF/ELF defendants, Catholic Worker peace activists, and anarchist
# prisoners. Seven verified portraits found:
#
#  - Rod Coronado: CC BY 3.0 Commons still, "Rod Coronado speaking in 2014".
#  - Chelsea Gerlach: Eugene Weekly photograph captioned with her name.
#  - Jeff Dietrich: the LA Catholic Worker's own photo of him being
#    escorted by police at the September 2025 MDC blockade.
#  - David Pasquarelli: portrait from his own memorial site's gallery.
#  - Daniel Burns, Peter DeMott, Teresa Grady: cropped left-to-right from
#    the Alicia Solsman photograph captioned "From left are Daniel Burns,
#    Peter DeMott, Teresa Grady and Clare Grady" (Metroactive, 2005).
#
# Fill-if-empty; idempotent.
#
# Run from the repo root:  bash database/data/photos-2000s-50.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
\Illuminate\Support\Facades\Storage::disk("public")->makeDirectory("prisoners");
$photos = [
    "rodney-coronado" => "rodney-coronado.jpg",
    "chelsea-gerlach" => "nonfree/chelsea-gerlach.jpg",
    "jeff-dietrich" => "nonfree/jeff-dietrich.jpg",
    "david-pasquarelli" => "nonfree/david-pasquarelli.jpg",
    "daniel-burns" => "nonfree/daniel-burns.jpg",
    "peter-demott" => "nonfree/peter-demott.jpg",
    "teresa-grady" => "nonfree/teresa-grady.jpg",
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
echo "Done. 2000s-era photo batch applied."
