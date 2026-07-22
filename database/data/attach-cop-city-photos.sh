#!/usr/bin/env bash
#
# Attach photos to Stop Cop City / Defend the Atlanta Forest defendants who
# had none.
#
# Photo coverage for this cohort is thin: unlike the historical/Jericho
# prisoners (whose own campaigns publish portraits), most Cop City defendants
# have no self-published photo, and the movement generally discourages
# circulating defendants' faces. A per-person search of ~61 photoless records
# found a usable, single-person, correctly-labeled image for only 13:
#   - 2 self-published headshots (Noah Grigni; Rukia Rogers, a school founder)
#   - 11 public county booking photos (DeKalb / Atlanta PD), the sanctioned
#     fallback, taken from mainstream local-news galleries (FOX 5/6).
# Multi-person mugshot grids/collages, "Free X" rally photos, wire/paywalled
# images, and images from hostile doxing sites were all rejected. Files live
# in database/data/photos/cop-city/ and are mapped in
# database/data/cop-city-photos.json.
#
# Idempotent: sets a photo only where the record currently has none. Run from
# the repo root:
#   bash database/data/attach-cop-city-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$rows = json_decode(file_get_contents(base_path("database/data/cop-city-photos.json")), true);
if (! is_array($rows)) { echo "Could not read mapping JSON\n"; return; }

$set = 0; $skip = 0; $missing = 0;
foreach ($rows as $r) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $r["slug"])->first();
    if (! $p) { echo "  not found: {$r["slug"]}\n"; $missing++; continue; }
    if (! empty($p->photo)) { $skip++; continue; }

    $src = base_path("database/data/photos/cop-city/" . $r["file"]);
    if (! is_file($src)) { echo "  file missing: {$r["file"]}\n"; continue; }
    $rel = "prisoners/cop-city/" . $r["file"];
    $dst = storage_path("app/public/" . $rel);
    @mkdir(dirname($dst), 0755, true);
    copy($src, $dst);
    $p->photo = $rel;
    $p->save();
    echo "  set {$p->slug} ({$r["type"]})\n";
    $set++;
}

echo "Set {$set}; skipped {$skip} that already had a photo; {$missing} not found.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Cop City defendant photos attached (13)."
