#!/usr/bin/env bash
#
# BATCH 166 -- John Gregory Jacobs: the Days of Rage portrait.
#
#   THE PHOTOGRAPH the curator supplied is the Getty editorial frame by
#   David Fenton, Chicago, October 11, 1969: Jacobs in a football helmet
#   speaking into a reporter microphone at the defaced base of the
#   Haymarket police statue the Weathermen had bombed three days
#   earlier. 300x401, about 10 KB, uncropped and unretouched.
#
#   THE IDENTIFICATION was checked before this was attached, because the
#   file arrived as an unattributed Google image-cache thumbnail and a
#   filename is not evidence. The distributor caption names him and
#   describes the helmet; the plinth is in the frame; and the date is
#   the day of the final march, which is the day of the arrest recorded
#   in batch 165 and the scene his biography already describes from a
#   contemporary eyewitness account. The photograph and the case record
#   agree with each other.
#
#   IT IS NOT A FREE IMAGE, and this batch does not pretend otherwise.
#   Rights-managed, not released, named photographer, no NPPC licence.
#   Every other portrait in this archive is public domain or no-known-
#   copyright. The fair-use reasoning for this one is written out in
#   database/data/photos/CREDITS-john-jacobs.md rather than left
#   implicit, so that whoever revisits it can weigh it. There is no
#   known free substitute: he spent twenty-seven years avoiding cameras,
#   and almost every surviving image of him is a press photograph from
#   these four days.
#
#   Idempotent: the file is copied only when it differs, and photo is
#   set to a fixed path.
#
# Run from the repo root, after git pull (after batch 165):
#   bash database/data/run-batch-166.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"; shift
    echo; echo "--- ${label}"
    if "$@"; then return 0; fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}"); return 0
}

echo "==================================================================="
echo "  Batch 166 — John Gregory Jacobs: the Days of Rage portrait"
echo "==================================================================="

FILE="john-jacobs.jpg"
SRC="database/data/files/${FILE}"
DEST="storage/app/public/prisoners/${FILE}"

install_photo() {
    if [ ! -f "$SRC" ]; then echo "  source missing: $SRC"; return 1; fi
    mkdir -p storage/app/public/prisoners
    if [ -f "$DEST" ] && cmp -s "$SRC" "$DEST"; then
        echo "  already installed, identical"
    else
        cp "$SRC" "$DEST"
        echo "  installed: $DEST"
    fi
    ls -l "$DEST"
    head -c 2 "$DEST" | od -An -tx1 | grep -q 'ff d8' && echo "  header check: JPEG" \
        || { echo "  !! not a JPEG"; return 1; }
    [ -e "public/storage/prisoners/${FILE}" ] && echo "  reachable through the public symlink" \
        || { echo "  !! NOT reachable — run php artisan storage:link"; return 1; }
}

update_record() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch166.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withUnderReview()->where("slug", $payload["slug"])->first();

if (! $p) { echo "  ", $payload["slug"], " NOT FOUND — nothing changed.\n"; return; }

echo "  record:       ", $p->name, "  [", $p->slug, "]\n";

// Batch 165 renamed him. If that has not been applied yet the photo would be
// attached to a record still carrying the old name and the old dates, which is
// worth saying out loud rather than discovering later.
if ($p->name !== $payload["expect_name"]) {
    echo "  NOTE: expected ", $payload["expect_name"], " — batch 165 may not have run yet.\n";
}

echo "  photo before: ", ($p->photo ?: "(none)"), "\n";

$p->photo = $payload["photo_path"];
$p->save();
$p->refresh();

echo "  photo after:  ", $p->photo, "\n";

$path = Storage::disk("public")->path($p->photo);

echo "  on disk:      ", (is_file($path) ? "yes, ".filesize($path)." bytes" : "NO — the file is missing"), "\n";
echo "  public url:   ", ($p->photoUrl() ?: "(none)"), "\n";
echo "  credit:       ", $payload["credit"], "\n";
echo "  rights:       rights-managed, no NPPC licence. See\n";
echo "                database/data/photos/CREDITS-john-jacobs.md\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "install-photo" install_photo
run "update-record" update_record

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 166 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "Check the portrait appears on /prisoner/john-jacobs. The photo_url"
echo "accessor cache-busts on file mtime, so a later replacement at the"
echo "same path will show without a cache purge."
