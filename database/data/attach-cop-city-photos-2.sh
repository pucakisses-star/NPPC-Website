#!/usr/bin/env bash
#
# Second batch of photos for Stop Cop City / Defend the Atlanta Forest
# defendants who had none: 19 official booking photos.
#
# Source: DeKalb County Sheriff / Atlanta Police Department booking photos
# (Georgia public records), taken from the individually-named files in the
# FOX 5 Atlanta (WAGA) and FOX 5 arrestee galleries — each file is labeled by
# the arrestee's own name, so identity is authoritative. All were viewed to
# confirm they are single-person mugshots. Mapping in
# database/data/cop-city-photos-2.json.
#
# Only public-record booking photos and mainstream local-news galleries were
# used. No doxxing/surveillance sites (antifawatch, Post Millennial/Ngo) and no
# commercial photo agencies (Getty/AP/Reuters) were used. Defendants whose only
# images live on those forbidden sources, or in a booking-grid whose per-face
# positions could not be verified, were left without a photo.
#
# Idempotent: sets a photo only where the record currently has none. Run from
# the repo root:
#   bash database/data/attach-cop-city-photos-2.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRCDIR="database/data/photos/cop-city"
DSTDIR="storage/app/public/prisoners/cop-city"
mkdir -p "$DSTDIR"

php artisan tinker --execute='
$rows = json_decode(file_get_contents(base_path("database/data/cop-city-photos-2.json")), true);
if (! is_array($rows)) { echo "Could not read mapping JSON\n"; return; }

$set = 0; $skip = 0; $missing = 0;
foreach ($rows as $r) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $r["slug"])->first();
    if (! $p) { echo "  not found: {$r["slug"]}\n"; $missing++; continue; }
    if (! empty($p->photo)) { $skip++; continue; }

    $src = base_path("database/data/photos/cop-city/" . $r["file"]);
    if (! is_file($src)) { echo "  file missing: {$r["file"]}\n"; continue; }
    $rel = "prisoners/cop-city/" . $r["file"];
    copy($src, storage_path("app/public/" . $rel));
    $p->photo = $rel;
    $p->save();
    echo "  set {$p->slug}\n";
    $set++;
}

echo "Set {$set}; skipped {$skip} that already had a photo; {$missing} not found.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Cop City defendant photos attached (batch 2, 19)."
