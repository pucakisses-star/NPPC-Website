#!/usr/bin/env bash
#
# Portrait for Macy (Elkins) Morse, January 25, 1921 - July 18, 2019 -- the
# Nashua, New Hampshire mother of thirteen who turned to pacifism after three
# of her sons went into the military and one to Vietnam, and who kept getting
# arrested for the next fifty years.
#
# Source: her obituary at Farwell Funeral Service, Nashua
#   https://farwellfuneralservice.com/macy-e-morse/
#   image: wp-content/uploads/Morse-Macy-photo-for-prayer-card.jpg
#
# The file is a CMYK prayer-card scan; converted to RGB and cropped from a
# full-length studio portrait to head and shoulders. A single-subject
# photograph published as her own memorial image, so there is no
# identification question.
#
# WORTH KNOWING, NOT FIXED HERE
#   Her AVCO Plowshares case carries an incarceration date of July 13, 1983 and
#   no release date, so the profile page renders
#   "Imprisoned For 43 years 0 months 14 days" -- for a woman the same case
#   record says was resentenced in 1990 to time served, about two weeks. She is
#   one of the 561 records with a stale duration column;
#   prisoners:recompute-imprisonment --apply drops it to no counter, since a
#   released prisoner with no release date has an unknown end. The "about two
#   weeks" stays in the sentence text, which is the only place it is actually
#   documented.
#
#   Her obituary also confirms jail time for the 1981 blood-splashing in
#   Alexander Haig’s office and the 2003 trespass in Senator Judd Gregg’s
#   office. Neither is on her record as a case; the bio mentions them. Dates
#   would be needed before they could be added properly.
#
# Only attached where the record has no photo; FORCE=1 replaces regardless.
#
# Idempotent. Run from the repo root:
#   bash database/data/attach-macy-morse-photo.sh
#   FORCE=1 bash database/data/attach-macy-morse-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

FORCE="${FORCE:-0}" php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$force = getenv("FORCE") === "1";

$matches = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->whereIn("slug", ["macy-morse", "macy-e-morse"])
        ->orWhereRaw("LOWER(name) IN (?, ?)", ["macy morse", "macy e. morse"]))
    ->get();

if ($matches->isEmpty()) { echo "NOT FOUND: Macy Morse\n"; exit(1); }
if ($matches->count() > 1) {
    echo "ABORT: {$matches->count()} records match. Refusing to guess:\n";
    foreach ($matches as $m) { echo "  {$m->slug}  {$m->name}\n"; }
    exit(1);
}

$p = $matches->first();

if ($p->photo && ! $force) {
    echo "{$p->name}: already has a photo ({$p->photo}) -- left alone. FORCE=1 to replace.\n";
    exit(0);
}

$src = base_path("database/data/photos/macy-morse.jpg");
if (! is_file($src)) { echo "MISSING SOURCE: database/data/photos/macy-morse.jpg\n"; exit(1); }

$dstRel = "prisoners/{$p->slug}.jpg";
File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
File::copy($src, storage_path("app/public/{$dstRel}"));
touch(storage_path("app/public/{$dstRel}"));   // bust the ?v=mtime photo cache
$p->photo = $dstRel;
$p->save();

echo "{$p->name}  [{$p->slug}]\n";
echo "  photo set -> {$dstRel}  (memorial portrait, Farwell Funeral Service)\n";

$days = $p->cases()->sum("imprisoned_for_days");
if ($days > 3650) {
    echo "\n  NOTE: her cases still sum to {$days} days. Stale column -- run:\n";
    echo "        php artisan prisoners:recompute-imprisonment --slug={$p->slug} --apply\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
