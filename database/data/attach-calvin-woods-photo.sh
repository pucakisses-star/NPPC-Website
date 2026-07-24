#!/usr/bin/env bash
#
# Attach a portrait of the Rev. Calvin Woods (Birmingham pastor and leader of
# the Alabama Christian Movement for Human Rights; jailed in January 1959 for
# urging his congregation to stop riding the city's Jim Crow buses).
#
# Photo: a later-life news portrait of Rev. Woods from al.com (Alabama Media
# Group), added at the site owner's request. It is copyrighted, not freely
# licensed, and is used at low resolution under the same non-commercial
# fair-use / political-prisoner memorial rationale as the other Birmingham
# ACMHR portraits already carried in photos/nonfree/ (Fred Shuttlesworth,
# Charles Billups). See database/data/photos/CREDITS-nonfree.md.
#
# Sets the photo only if Calvin Woods currently has none (never overwrites an
# existing image). Idempotent. Run from the repo root:
#   bash database/data/attach-calvin-woods-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/photos/nonfree/calvin-woods.jpg"
DST="storage/app/public/prisoners/calvin-woods.jpg"
mkdir -p "$(dirname "$DST")"

if [ -f "$SRC" ] && [ ! -f "$DST" ]; then
    cp "$SRC" "$DST"
    echo "copied calvin-woods.jpg"
fi

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()
    ->where("slug", "calvin-woods")
    ->orWhereRaw("LOWER(name) = ?", ["calvin woods"])
    ->first();

if (! $p) {
    echo "Calvin Woods not found.\n";
} elseif (! empty($p->photo)) {
    echo "Calvin Woods already has a photo ({$p->photo}) — leaving alone.\n";
} elseif (! is_file(storage_path("app/public/prisoners/calvin-woods.jpg"))) {
    echo "Image file missing at storage/app/public/prisoners/calvin-woods.jpg.\n";
} else {
    $p->photo = "prisoners/calvin-woods.jpg";
    $p->save();
    echo "SET photo on {$p->name} (slug: {$p->slug}).\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Calvin Woods portrait attached."
