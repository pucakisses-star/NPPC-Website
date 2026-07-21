#!/usr/bin/env bash
#
# Upgrade Cody Kretsinger's record with the portrait and links from his own
# website (codykretsinger.com):
#   - Replace the low-res TV-news still with his high-res site portrait.
#   - Set website + twitter. (The prisoners table only has website/twitter/
#     facebook/instagram columns, so his LinkedIn, GitHub, Bluesky and
#     Reddit links have no field to live in.)
#
# The portrait ships in database/data/audit-photos/; this OVERWRITES the
# stored file. Idempotent for the links (only sets when empty).
#   bash database/data/update-kretsinger-photo-socials.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/audit-photos/cody-andrew-kretsinger.jpg"
DST="storage/app/public/prisoners/cody-andrew-kretsinger.jpg"
mkdir -p "$(dirname "$DST")"
cp -f "$SRC" "$DST"
echo "Overwrote $DST with the site portrait."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "cody-andrew-kretsinger")->first();
if (! $p) { echo "cody-andrew-kretsinger not found\n"; return; }

// Photo: point at the (now overwritten) file even if it was already set.
$p->photo = "prisoners/cody-andrew-kretsinger.jpg";

if (empty($p->website)) { $p->website = "https://codykretsinger.com"; echo "SET website\n"; }
if (empty($p->twitter)) { $p->twitter = "https://twitter.com/CodyKretsinger"; echo "SET twitter\n"; }

$p->save();
echo "Saved cody-andrew-kretsinger.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Kretsinger photo + socials updated."
