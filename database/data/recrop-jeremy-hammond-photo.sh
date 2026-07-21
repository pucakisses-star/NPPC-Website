#!/usr/bin/env bash
#
# Replace Jeremy Hammond's portrait with a version cropped in from the left
# and right (620x412 -> 480x412), trimming the dead grey space on either
# side to tighten the framing. The photo field already points at
# prisoners/jeremy-hammond.webp, so this just overwrites the stored file.
#
# Run from the repo root:
#   bash database/data/recrop-jeremy-hammond-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/audit-photos/jeremy-hammond.webp"
DST="storage/app/public/prisoners/jeremy-hammond.webp"

if [ ! -f "$SRC" ]; then
    echo "Source $SRC missing" >&2
    exit 1
fi

mkdir -p "$(dirname "$DST")"
cp -f "$SRC" "$DST"
echo "Overwrote $DST with the re-cropped portrait."

php artisan tinker --execute='
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Cache cleared.\n";
'

echo
echo "Done. Jeremy Hammond photo re-cropped."
