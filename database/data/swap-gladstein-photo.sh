#!/usr/bin/env bash
#
# Correct Richard Gladstein's portrait: the earlier crop took the wrong figure
# (rightmost man). Gladstein is the man on the LEFT (taking notes) in the SFPL
# courtroom photograph (sfphotos AAA-5992). This overwrites the existing photo
# with the left-figure crop. Run from the repo root:
#   bash database/data/swap-gladstein-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$src = base_path("database/data/photos/nonfree/richard-gladstein.jpg");
if (! is_file($src)) { echo "Photo file missing.\n"; return; }
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "richard-gladstein")->first();
if (! $p) { echo "richard-gladstein not found.\n"; return; }

@mkdir(storage_path("app/public/prisoners"), 0775, true);
$rel = "prisoners/richard-gladstein.jpg";
copy($src, storage_path("app/public/" . $rel));   // overwrite the stored file
$p->photo = $rel;
$p->save();
echo "Swapped richard-gladstein photo to the left-figure crop.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Richard Gladstein photo corrected to the man on the left."
