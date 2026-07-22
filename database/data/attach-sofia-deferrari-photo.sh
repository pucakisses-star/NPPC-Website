#!/usr/bin/env bash
#
# Attach Sofia DeFerrari's photo and support-page link.
#
# Sofia DeFerrari (aka Sofia "Candle" Johnson, "Comrade Candle") is an Oregon
# anarchist prisoner (Coffee Creek). The photo is the campaign selfie used
# across her solidarity write-ups, taken from the openly-accessible Anarchist
# Federation post announcing her support site; it is committed to the repo at
# database/data/audit-photos/sofia-deferrari.jpg.
#
# Her original support site (freesofiajohnson.com) has lapsed and now serves a
# spam/parked page, so it is NOT linked. The website is set instead to the
# live Anarchist Federation author archive, which carries her ongoing
# "Written From The Inside" letters.
#
# Idempotent: sets the photo only if she has none, and the website only if
# empty. Run from the repo root (after the sofia-johnson -> sofia-deferrari
# merge):
#   bash database/data/attach-sofia-deferrari-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/audit-photos/sofia-deferrari.jpg"
DST="storage/app/public/prisoners/sofia-deferrari.jpg"
mkdir -p "$(dirname "$DST")"
cp -f "$SRC" "$DST"
echo "Installed $DST."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "sofia-deferrari")->first();
if (! $p) { echo "sofia-deferrari not found (has the merge run yet?)\n"; return; }

if (empty($p->photo) && is_file(storage_path("app/public/prisoners/sofia-deferrari.jpg"))) {
    $p->photo = "prisoners/sofia-deferrari.jpg";
    echo "SET photo\n";
}
if (empty($p->website)) {
    $p->website = "https://www.anarchistfederation.net/author/freesofiadeferrari/";
    echo "SET website\n";
}
if ($p->isDirty()) { $p->save(); echo "saved.\n"; } else { echo "nothing to do (already set).\n"; }

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Sofia DeFerrari photo and support link attached."
