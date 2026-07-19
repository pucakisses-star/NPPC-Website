#!/usr/bin/env bash
#
# Photo hunt over the third sort-order slice of photoless prisoners
# (July 2026) — 50 more records, again dominated by 2020s protest
# defendants. Three caption-certified portraits found, and one more
# duplicate pair surfaced and merged.
#
#  - Carlos Alfredo Fugarte: Polk County booking photograph, captioned
#    with his name by Fox 13 Tampa Bay.
#  - Cortez Aaron Rice: Waukesha County booking photograph, from the
#    Fox News split image captioned "Cortez Rice (right)".
#  - Elijah Gantt: AP photograph of his August 9, 2024 arrest outside
#    the Ferguson police department, captioned with his name.
#  - Merge: davon-de-andre-turner <= davon-deandre-turner (the same
#    Minneapolis Third Precinct arson defendant under both spellings;
#    the dup's federal case reassigns to the canonical, which had none).
#
# Fill-if-empty; idempotent.
#
# Run from the repo root:  bash database/data/photos-sort-order-50b.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoners:merge-duplicates --only=davon-de-andre-turner --apply

php artisan tinker --execute='
\Illuminate\Support\Facades\Storage::disk("public")->makeDirectory("prisoners");
$photos = [
    "carlos-alfredo-fugarte" => "nonfree/carlos-alfredo-fugarte.jpg",
    "cortez-aaron-rice" => "nonfree/cortez-aaron-rice.jpg",
    "elijah-gantt" => "nonfree/elijah-gantt.jpg",
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
echo "Done. Sort-order photo batch 3 applied."
