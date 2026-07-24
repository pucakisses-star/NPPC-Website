#!/usr/bin/env bash
#
# Enrich Abraham Isaak Jr. (1883-1953): attach his portrait, set birth/death
# dates, and expand his biography (site owner's text).
#
# Photo: a portrait line-drawing via the Jane Addams Digital Edition
# (s3.amazonaws.com/omeka-janeaddams/.../bfcf33c08a1b425fd41b96ea8cde1809.JPG);
# an early-1900s newspaper image, public domain, cropped to remove the
# "Abraham Isaak Jr" caption.
#
# Idempotent. Run from the repo root:
#   bash database/data/update-abraham-isaak-jr.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/photos/abraham-isaak-jr.jpg"
DST="storage/app/public/prisoners/abraham-isaak-jr.jpg"
mkdir -p "$(dirname "$DST")"
if [ -f "$SRC" ] && [ ! -f "$DST" ]; then cp "$SRC" "$DST"; echo "copied abraham-isaak-jr.jpg"; fi

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()
    ->where("slug", "abraham-isaak-jr")
    ->orWhereRaw("LOWER(name) = ?", ["abraham isaak jr."])
    ->first();

if (! $p) { echo "Abraham Isaak Jr. not found.\n"; return; }

$p->birthdate = "1883-01-25";
$p->death_date = "1953-08-30";
$p->description = "Abraham Isaak Jr. was the son of the anarchist publisher Abraham Isaak. He was born in Russia and immigrated with his family to San Francisco in 1891. His first job was on the family anarchist paper, Free Society, as a compositor and co-publisher. In 1901 he was arrested in Chicago alongside his father and several other anarchists in the McKinley assassination case, on a conspiracy charge; after several weeks he was released. The Isaaks left Chicago for New York City, where they tried to sustain Free Society, but printing stopped in 1904. He went on to work in the drug industry as a clerk and buyer, first in Manhattan and later in Mount Vernon. He married Rose Isaak in 1911, and they had two sons. Abraham Isaak Jr. died in 1953.";
if (is_file(storage_path("app/public/prisoners/abraham-isaak-jr.jpg"))) {
    $p->photo = "prisoners/abraham-isaak-jr.jpg";
}
$p->save();

echo "Updated {$p->name}: photo={$p->photo}, born {$p->birthdate}, died {$p->death_date}\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Abraham Isaak Jr. portrait, dates and bio updated."
