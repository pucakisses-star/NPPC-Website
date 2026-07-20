#!/usr/bin/env bash
#
# Attach a portrait of George Marshall (1904-2000; civil-liberties
# activist, Sierra Club president) and finish the Max Obuszewski name
# split.
#
# George Marshall photo: cropped from the group photo of the three
# Marshall brothers (George, James, and Bob Marshall) on the LocalWiki
# History San Leandro / Highland "hsl" page
# https://localwiki.org/hsl/George_Marshall — LocalWiki content is
# Creative Commons BY licensed; George is the figure on the left. The
# cropped file ships in database/data/audit-photos/.
#
# Obuszewski: keep the display name "Max Obuszewski" but populate the
# split name parts (first Maximilian, middle J., last Obuszewski).
#
# Idempotent. Run from the repo root:
#   bash database/data/attach-george-marshall-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/audit-photos"
DST="storage/app/public/prisoners"
mkdir -p "$DST"

if [ -f "$SRC/george-marshall.jpg" ] && [ ! -f "$DST/george-marshall.jpg" ]; then
    cp "$SRC/george-marshall.jpg" "$DST/george-marshall.jpg"
    echo "copied george-marshall.jpg"
fi

php artisan tinker --execute='
$g = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "george-marshall")->first();
if ($g && empty($g->photo) && is_file(storage_path("app/public/prisoners/george-marshall.jpg"))) {
    $g->photo = "prisoners/george-marshall.jpg";
    $g->save();
    echo "SET photo on george-marshall\n";
}

$m = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "max-obuszewski")->first();
if ($m) {
    $m->name = "Max Obuszewski";
    $m->first_name = "Maximilian";
    $m->middle_name = "J.";
    $m->last_name = "Obuszewski";
    $m->save();
    echo "SET Obuszewski name parts (display name kept as Max Obuszewski)\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. George Marshall photo attached; Max Obuszewski name parts set."
