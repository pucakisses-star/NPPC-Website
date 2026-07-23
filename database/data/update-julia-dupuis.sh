#!/usr/bin/env bash
#
# Julia Dupuis (Stop Cop City / Defend the Atlanta Forest defendant):
#   - add her middle name, Caroline (confirmed by the Bartow County jail
#     record: "DUPUIS, JULIA CAROLINE", booked 4/28/2023).
#   - attach a portrait. Her own MyFitnessPal byline image was hotlink-blocked
#     and could not be fetched, so this uses her self-published Muck Rack
#     journalist-profile portrait (bio: "writer, storyteller, & activist facing
#     RICO charges for #StopCopCity"), which confirms identity. Swap the file
#     at database/data/photos/cop-city/julia-dupuis.jpg to change the image.
#
# Sets the middle name (and first/last if empty) and the photo only if empty.
# Idempotent. Run from the repo root:
#   bash database/data/update-julia-dupuis.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

mkdir -p storage/app/public/prisoners/cop-city
cp -f database/data/photos/cop-city/julia-dupuis.jpg storage/app/public/prisoners/cop-city/julia-dupuis.jpg
echo "Installed Julia Dupuis photo."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "julia-dupuis")->first();
if (! $p) { echo "julia-dupuis not found.\n"; return; }

$p->middle_name = "Caroline";
if (empty($p->first_name)) { $p->first_name = "Julia"; }
if (empty($p->last_name)) { $p->last_name = "Dupuis"; }

if (empty($p->photo) && is_file(storage_path("app/public/prisoners/cop-city/julia-dupuis.jpg"))) {
    $p->photo = "prisoners/cop-city/julia-dupuis.jpg";
    echo "SET photo\n";
}

$p->save();
echo "Set middle name Caroline for {$p->name}.\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Julia Dupuis updated."
