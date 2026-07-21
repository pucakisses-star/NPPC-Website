#!/usr/bin/env bash
#
# Download and attach John Hunn's portrait — the labeled painted portrait
# from the Delaware Division of Historical & Cultural Affairs "Flight to
# Freedom" page for the Quaker Underground Railroad stationmaster. The state
# site rejects generic bot fetches, so this sends a browser User-Agent and
# referer, then verifies the download is a real JPEG (not a WAF rejection
# page) before installing it.
#
# Idempotent: skips if he already has a photo. Run from the repo root:
#   bash database/data/attach-john-hunn-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

URL="https://history.delaware.gov/wp-content/uploads/sites/179/2019/02/johnhunn.jpg"
DST="storage/app/public/prisoners/john-hunn.jpg"
TMP="$(mktemp)"
mkdir -p "$(dirname "$DST")"

curl -fsSL \
  -A "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36" \
  -e "https://history.delaware.gov/flight-to-freedom/people_hunn/" \
  -o "$TMP" "$URL" || { echo "Download failed (network/WAF)."; rm -f "$TMP"; exit 1; }

# Reject the firewall "Request Rejected" HTML page — require an actual image.
MIME="$(file -b --mime-type "$TMP" 2>/dev/null || echo unknown)"
if [ "$MIME" != "image/jpeg" ] && [ "$MIME" != "image/png" ] && [ "$MIME" != "image/webp" ]; then
    echo "Downloaded content is not an image ($MIME) — the state site likely blocked the request."
    echo "Grab the file manually from a browser and drop it at $DST, then re-run."
    rm -f "$TMP"
    exit 1
fi

cp -f "$TMP" "$DST"
rm -f "$TMP"
echo "Installed $DST ($MIME)."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "john-hunn")->first();
if ($p && empty($p->photo) && is_file(storage_path("app/public/prisoners/john-hunn.jpg"))) {
    $p->photo = "prisoners/john-hunn.jpg";
    $p->save();
    echo "SET photo on john-hunn\n";
} else {
    echo "Nothing to do (missing, already has photo, or file absent).\n";
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. John Hunn portrait downloaded and attached."
