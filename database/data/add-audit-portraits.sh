#!/usr/bin/env bash
#
# From the NPPC missing-photo audit: attach the two records for which an
# automated pass could retrieve a clean, correctly-identified single
# portrait and a human confirmed the identity by sight.
#
#   rik-scarce         -> Skidmore College sociology faculty portrait
#   stellan-vinthagen  -> University of Gothenburg staff portrait
#
# Both are third-party (university) portraits; the site owner accepted the
# reuse. The image files ship in database/data/audit-photos/ and are copied
# into the public storage disk here.
#
# The audit's remaining rows could NOT be auto-attached: their sources were
# group photos, full newspaper pages, placeholder avatars, a crossword,
# wrong-person matches, PDFs needing manual crop, or paywalled commercial
# licensing platforms (Reuters/Bridgeman). Those need files supplied by
# hand — see the session notes.
#
# Idempotent. Run from the repo root:  bash database/data/add-audit-portraits.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/audit-photos"
DST="storage/app/public/prisoners"
mkdir -p "$DST"

for slug in rik-scarce stellan-vinthagen; do
    if [ -f "$SRC/$slug.jpg" ] && [ ! -f "$DST/$slug.jpg" ]; then
        cp "$SRC/$slug.jpg" "$DST/$slug.jpg"
        echo "copied $slug.jpg"
    fi
done

php artisan tinker --execute='
$set = function (string $slug) {
    $file = "prisoners/{$slug}.jpg";
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if ($p && empty($p->photo) && is_file(storage_path("app/public/".$file))) {
        $p->photo = $file;
        $p->save();
        echo "SET photo on {$slug}\n";
    }
};
$set("rik-scarce");
$set("stellan-vinthagen");

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Audit portraits attached (Rik Scarce, Stellan Vinthagen)."
