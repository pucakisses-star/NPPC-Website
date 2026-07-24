#!/usr/bin/env bash
#
# Attach a portrait for Richard Gladstein, cropped (rightmost figure, per the
# site owner) from a San Francisco Public Library historical courtroom
# photograph (SFPL sfphotos AAA-5992). Only fills the record if it has no photo.
# Idempotent. Run from the repo root:
#   bash database/data/attach-gladstein-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$src = base_path("database/data/photos/nonfree/richard-gladstein.jpg");
if (! is_file($src)) { echo "Photo file missing.\n"; return; }
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "richard-gladstein")->first();
if (! $p) { echo "richard-gladstein not found.\n"; return; }
if (! empty($p->photo)) { echo "Already has a photo; leaving as-is.\n"; return; }

@mkdir(storage_path("app/public/prisoners"), 0775, true);
$rel = "prisoners/richard-gladstein.jpg";
copy($src, storage_path("app/public/" . $rel));
$p->photo = $rel;
$p->save();
echo "Photo attached to richard-gladstein.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Richard Gladstein photo attached."
