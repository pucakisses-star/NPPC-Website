#!/usr/bin/env bash
#
# Abraham Isaak (1856-1937), Chicago anarchist and editor of Free Society,
# detained in the roundup following the assassination of President McKinley.
# This:
#   1) attaches his portrait (fill-if-empty),
#   2) fills his birth (Oct 4, 1856) and death (Dec 10, 1937) dates, and
#   3) sets his custody dates: incarcerated September 6, 1901 at the Cook County
#      Jail, Chicago; released September 23, 1901 (17 days), without prosecution.
#
# Photo: portrait from the Chicago Daily Tribune, September 9, 1901, p. 3, via
# the Jane Addams Digital Edition (digital.janeaddams.ramapo.edu/items/show/893).
# The 1901 newspaper image is public domain; cropped from the oval vignette
# (masthead text and the "Abraham Isaak Sr." caption trimmed out).
#
# Idempotent. Run from the repo root:
#   bash database/data/add-abraham-isaak-details.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/photos/abraham-isaak.jpg"
DST="storage/app/public/prisoners/abraham-isaak.jpg"
mkdir -p "$(dirname "$DST")"
if [ -f "$SRC" ] && [ ! -f "$DST" ]; then cp "$SRC" "$DST"; echo "copied abraham-isaak.jpg"; fi

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()
    ->where("slug", "abraham-isaak")
    ->orWhereRaw("LOWER(name) = ?", ["abraham isaak"])
    ->first();

if (! $p) { echo "Abraham Isaak not found.\n"; return; }

if (empty($p->birthdate)) { $p->birthdate = "1856-10-04"; echo "SET birthdate\n"; }
if (empty($p->death_date)) { $p->death_date = "1937-12-10"; echo "SET death_date\n"; }
$p->in_custody = false;
$p->released = true;
if (empty($p->photo) && is_file(storage_path("app/public/prisoners/abraham-isaak.jpg"))) {
    $p->photo = "prisoners/abraham-isaak.jpg";
    echo "SET photo\n";
}
$p->save();

$jail = \App\Models\Institution::firstOrCreate(["name"=>"Cook County Jail"], ["city"=>"Chicago","state"=>"Illinois"]);
$c = $p->cases()->first();
if (! $c) { $c = new \App\Models\PrisonerCase(); $c->prisoner_id = $p->id; $c->charges = "Detained in the post-assassination anarchist roundup"; }
$c->institution_id = $jail->id;
$c->convicted = "No — released without prosecution (no evidence of a conspiracy)";
$c->setPartialDate("arrest_date", 1901, 9, 6);
$c->setPartialDate("incarceration_date", 1901, 9, 6);
$c->setPartialDate("release_date", 1901, 9, 23);
$note = "Arrested on the night of September 6, 1901 and held without bail at the Cook County Jail in the roundup after President McKinley was shot; released September 23, 1901 (17 days) once authorities found no evidence linking him to a conspiracy.";
$cur = (string) $c->sentence;
if (strpos($cur, $note) === false) { $c->sentence = $cur === "" ? $note : ($cur." — ".$note); }
$c->save();

echo "{$p->name}: incarcerated ".$c->partialDateIso("incarceration_date")." -> released ".$c->partialDateIso("release_date")." ({$c->imprisoned_for_days} days)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Abraham Isaak portrait, dates and custody set."
