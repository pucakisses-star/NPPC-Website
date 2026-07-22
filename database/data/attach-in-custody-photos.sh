#!/usr/bin/env bash
#
# Attach photos to currently-imprisoned prisoners who had none.
#
# An audit of the database found 67 in-custody records with no photo. A
# per-person search turned up an openly-sourced, identity-verified image for
# ten of them; the rest had only paywalled-agency images or no photo at all.
#
# Only openly-accessible sources were used (no commercial photo agencies):
#   - Government booking records (public records): Henry Parker (Sedgwick Co.),
#     Mujera Benjamin Lunga ho (Pulaski Co.), Shabazz Akeem Isiah Watson
#     (Charleston Co.), Pascale Ferrier (Hidalgo Co.), Mohammed Sabry Soliman
#     (Boulder PD).
#   - U.S. government work / public domain: Jamison Wagner (DOJ photo).
#   - Support-campaign or movement self-published: Joy Gibson and Rebecca
#     Morgan (Prairieland Defendants), Siddique Abdullah Hasan (Jericho
#     Movement), Brian Simpson (his own clemency site).
# News-graphic composites were cropped to the person. Every image was checked
# visually against the case facts. Per-image provenance is in
# database/data/in-custody-photos.json.
#
# Sets the photo only where the record currently has none. Idempotent. Run from
# the repo root:
#   bash database/data/attach-in-custody-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRCDIR="database/data/photos/in-custody"
DSTDIR="storage/app/public/prisoners/in-custody"
mkdir -p "$DSTDIR"
cp -f "$SRCDIR"/*.jpg "$DSTDIR"/ 2>/dev/null || true
echo "Copied in-custody photos into $DSTDIR."

php artisan tinker --execute='
$rows = json_decode(file_get_contents(base_path("database/data/in-custody-photos.json")), true);
if (! is_array($rows)) { echo "Could not read mapping JSON\n"; return; }

$set = 0; $skip = 0; $missing = 0;
foreach ($rows as $r) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $r["slug"])->first();
    if (! $p) { echo "  not found: {$r["slug"]}\n"; $missing++; continue; }
    if (! empty($p->photo)) { $skip++; continue; }

    $rel = "prisoners/in-custody/" . $r["file"];
    if (! is_file(storage_path("app/public/" . $rel))) { echo "  file missing: {$r["file"]}\n"; continue; }
    $p->photo = $rel;
    $p->save();
    echo "  set {$p->slug}  (" . $r["license"] . ")\n";
    $set++;
}

echo "Set {$set}; skipped {$skip} that already had a photo; {$missing} not found.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. In-custody prisoner photos attached (10)."
