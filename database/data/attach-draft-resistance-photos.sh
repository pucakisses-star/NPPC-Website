#!/usr/bin/env bash
#
# Attach photos to the Vietnam-era draft-resistance prisoners added in
# database/data/add-draft-resistance.sh.
#
# Only openly-sourced images are used (no commercial photo-agency material):
#   - Gary Rader: PUBLIC DOMAIN (Universal newsreel still, Wikimedia Commons).
#   - Frank Pommersheim: CC BY-SA 3.0 (Wikimedia Commons).
#   - Bruce Dancis: portrait he provided to Cornell Alumni Magazine.
#   - Barry Bondhus: 1966 local-newspaper photo via Courage to Resist.
#   - Joseph F. O Rourke: memorial portrait on a fellow member s movement blog.
#   - Joseph E. Mulligan: event portrait on a non-commercial site.
# Per-image provenance and license are in database/data/draft-resistance-photos.json.
#
# The well-documented Minnesota Eight group photo was deliberately NOT used:
# it is a Cheryl Walsh Bellville image marked all rights reserved, not an open
# license. Several others had only paywalled-agency images and were skipped.
#
# Matches each person by name AND a shared affiliation (so it targets the
# draft-resistance record, never an unrelated namesake), and sets the photo
# only where the record currently has none. Idempotent. Run from the repo root:
#   bash database/data/attach-draft-resistance-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRCDIR="database/data/photos/draft-resistance"
DSTDIR="storage/app/public/prisoners/draft-resistance"
mkdir -p "$DSTDIR"
cp -f "$SRCDIR"/*.jpg "$DSTDIR"/ 2>/dev/null || true
echo "Copied draft-resistance photos into $DSTDIR."

php artisan tinker --execute='
$rows = json_decode(file_get_contents(base_path("database/data/draft-resistance-photos.json")), true);
if (! is_array($rows)) { echo "Could not read photo mapping JSON\n"; return; }

$set = 0; $skip = 0; $missing = 0;
foreach ($rows as $r) {
    $cands = \App\Models\Prisoner::withoutGlobalScopes()
        ->whereRaw("LOWER(name) = ?", [strtolower($r["name"])])->get();
    $p = null;
    foreach ($cands as $c) {
        $aff = array_map("strtolower", (array) $c->affiliation);
        if (in_array(strtolower($r["affiliation"]), $aff, true)) { $p = $c; break; }
    }
    if (! $p) { echo "  not found: {$r["name"]} ({$r["affiliation"]})\n"; $missing++; continue; }
    if (! empty($p->photo)) { $skip++; continue; }

    $rel = "prisoners/draft-resistance/" . $r["file"];
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
echo "Done. Draft-resistance photos attached (6)."
