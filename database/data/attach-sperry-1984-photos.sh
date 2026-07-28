#!/usr/bin/env bash
#
# Portraits for John LaForge and Barbara Katt, cropped out of the 1984
# Minneapolis Star Tribune clipping in the LaForge archives, reproduced in
# Beyond Nuclear International, "The judge who assailed 'worship of the Bomb'"
# (December 1, 2019).
#
#   https://beyondnuclearinternational.org/wp-content/uploads/2019/11/star-tribune-headline-from-laforge-archives.png
#   Staff photo by Mike Zerby. Caption: "Supporters met John LaForge and
#   Barb Katt at the courthouse as they were sentenced for destroying computer
#   parts at Sperry, Inc."
#
# HOW THE TWO WERE TOLD APART -- read this before trusting the images.
#   The caption names two people and gives no left-to-right order. The frame
#   holds about seven, but only two are in focus with the press microphones
#   turned on them: a young clean-shaven man at centre and a woman in round
#   wire-rimmed glasses at right. The caption names one man and one woman, so
#   the man is LaForge and the woman is Katt. That is a deduction from the
#   caption, not a positive identification against a known portrait of either
#   of them -- no such portrait was found for comparison, and the researcher
#   who supplied the corrected Katt record judged this clipping "not a usable
#   individual portrait". Attached at the site owner’s direction. If either is
#   wrong, delete the file and clear the photo column.
#
# It is 1984 newsprint halftone, so both are grainy; contrast was stretched
# slightly and nothing else was altered.
#
# Only attached where the record has no photo; FORCE=1 replaces regardless.
#
# Idempotent. Run from the repo root:
#   bash database/data/attach-sperry-1984-photos.sh
#   FORCE=1 bash database/data/attach-sperry-1984-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

FORCE="${FORCE:-0}" php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$force = getenv("FORCE") === "1";

$map = [
    "john-laforge" => "john-laforge",
    "barbara-katt" => "barbara-katt",
];

$attached = 0; $skipped = 0;
foreach ($map as $slug => $file) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "NOT FOUND: {$slug}\n"; continue; }

    $src = base_path("database/data/photos/{$file}.jpg");
    if (! is_file($src)) { echo "MISSING SOURCE: {$file}.jpg\n"; continue; }

    if ($p->photo && ! $force) {
        echo "{$p->name}: already has a photo ({$p->photo}) -- left alone, FORCE=1 to replace\n";
        $skipped++;
        continue;
    }

    $dstRel = "prisoners/{$p->slug}.jpg";
    File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
    File::copy($src, storage_path("app/public/{$dstRel}"));
    touch(storage_path("app/public/{$dstRel}"));   // bust the ?v=mtime photo cache
    $p->photo = $dstRel;
    $p->save();
    echo "{$p->name}: photo set -> {$dstRel}\n";
    $attached++;
}

echo "\nIdentified from the caption plus the two faces the microphones are on,\n";
echo "not against a known portrait of either. Check them.\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done. Attached {$attached}, skipped {$skipped}.\n";
'

echo
echo "Done."
