#!/usr/bin/env bash
#
# Attaches a portrait to Rubin "Hurricane" Carter — cropped and
# brightened from Michael Borkson's CC BY-SA 2.0 photograph of Carter
# speaking at Bunker Hill Community College (Wikimedia Commons,
# File:Rubin_Carter.jpg; the Commons description identifies the
# subject). Fill-if-empty.
#
# Run from the repo root:  bash database/data/add-rubin-carter-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "rubin-carter")->first();
if ($p && empty($p->photo) && is_file(database_path("data/photos/rubin-carter.jpg"))) {
    \Illuminate\Support\Facades\Storage::disk("public")->put("prisoners/rubin-carter.jpg", (string) file_get_contents(database_path("data/photos/rubin-carter.jpg")));
    $p->photo = "prisoners/rubin-carter.jpg";
    $p->save();
    echo "PHOTO rubin-carter\n";
} else {
    echo "Skipped (missing record, existing photo, or missing file)\n";
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Rubin Carter photo attached."
