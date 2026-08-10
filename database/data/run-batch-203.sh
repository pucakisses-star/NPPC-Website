#!/usr/bin/env bash
#
# BATCH 203 -- the 1958 press photograph of Clennon King, under fair use.
#
#   THE CURATOR DIRECTED IT, and that is their call to make as publisher.
#   It also turns out this archive already has a lane for exactly this:
#   database/data/photos/nonfree/ holds 293 copyrighted portraits, each
#   credited in CREDITS-nonfree.md under a non-commercial fair-use and
#   political-prisoner memorial rationale, with a standing instruction to
#   remove any image if a rights-holder objects. Batch 202 left his photo
#   field empty on a stricter reading than the archive's own practice.
#   This corrects that.
#
#   THE PHOTOGRAPH is a period portrait of King reproduced by
#   face2faceafrica.com in its 2020 article on the Ole Miss incident --
#   published there as a scanned print with the film edge and sprocket
#   marks still visible down the left side, which is what makes it
#   legible as a copy of a 1958-era press photograph rather than a modern
#   picture. face2faceafrica is already a cited source in
#   CREDITS-nonfree.md, for the Pete O'Neal portrait.
#
#   CROPPED from the 886x625 scan to 470x440 head-and-shoulders, removing
#   the article's white matte, its drop-shadow frame and the film border.
#   The low resolution is part of the rationale, not an accident.
#
#   A BETTER SOURCE EXISTS AND IS RECORDED. The Mississippi Department of
#   Archives and History holds a photograph of King in the Sovereignty
#   Commission records, catalogued 1-28-0-94-1-1-1ph. That would be
#   better provenanced than a press scan reproduced by a news site. The
#   lead is written into the credits row so it does not get lost.
#
#   THE PATH IS KEYED ON THE SLUG, NOT THE NAME, and here that matters.
#   Batch 202 renames him to Clennon Washington King Jr. while the slug
#   stays clennon-king. The bulk command prisoners:attach-nonfree-photos
#   builds its path from Str::slug of the NAME, which would write to
#   prisoners/clennon-washington-king-jr.jpg -- somewhere the model never
#   looks, since photoUrl documents prisoners/{slug}.jpg. This batch
#   writes the path the model reads.
#
#   Idempotent: the file is copied only when absent or different, and the
#   photo field set only when it differs.
#
# Run from the repo root, after git pull, after batch 202:
#   bash database/data/run-batch-203.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

# tinker exits 0 even when the code inside throws; success is a sentinel
# the step prints as its last act.
run_tinker() {
    local label="$1" sentinel="$2" code="$3" out
    echo; echo "--- ${label}"
    out=$(php artisan tinker --execute="$code" 2>&1) || true
    printf '%s\n' "$out"
    if ! grep -q "$sentinel" <<<"$out"; then
        echo "  !! FAILED: ${label} — sentinel ${sentinel} missing (exception above?)"
        FAILED+=("${label}")
    fi
}

echo "==================================================================="
echo "  Batch 203 — Clennon King, 1958 press photograph (fair use)"
echo "==================================================================="

SRC="database/data/photos/nonfree/clennon-king.jpg"
DEST_DIR="storage/app/public/prisoners"

echo
echo "--- install-photo"
install_ok=1
mkdir -p "$DEST_DIR"
if [ ! -f "$SRC" ]; then
    echo "  missing source file: $SRC"; install_ok=0
else
    dest="$DEST_DIR/clennon-king.jpg"
    if [ -f "$dest" ] && cmp -s "$SRC" "$dest"; then
        echo "  clennon-king.jpg — already installed, identical"
    else
        cp "$SRC" "$dest"
        echo "  clennon-king.jpg — $(stat -c%s "$dest") bytes installed"
    fi
    if [ ! -e "public/storage/prisoners/clennon-king.jpg" ]; then
        echo "  !! not reachable through the public symlink — run php artisan storage:link"
        install_ok=0
    fi
fi
[ "$install_ok" -eq 1 ] || FAILED+=("install-photo")

ATTACH_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch203.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$ph = $payload["photo"];

$prisoner = Prisoner::withoutGlobalScopes()->where("slug", $ph["slug"])->first();

if (! $prisoner) {
    echo "  no prisoner at slug ", $ph["slug"], " — nothing changed.\n";

    return;
}

if (! Storage::disk("public")->exists($ph["to"])) {
    echo "  !! photo not on disk at ", $ph["to"], " — the install step above did not land.\n";

    return;
}

$was = $prisoner->photo ?: "(none)";

if ($prisoner->photo !== $ph["to"]) {
    $prisoner->photo = $ph["to"];
    $prisoner->save();
    $prisoner->refresh();
    echo "  photo: ", $was, "  ->  ", $prisoner->photo, "\n";
} else {
    echo "  photo already ", $prisoner->photo, " — nothing to do.\n";
}

$bytes = Storage::disk("public")->size($prisoner->photo);
$onDisk = $bytes > 0;

echo "\n  ", $prisoner->name, "  [/prisoner/", $prisoner->slug, "]\n";
echo "    photo    ", $prisoner->photo, "   ", $bytes, " bytes\n";
echo "    url      ", $prisoner->photoUrl(), "\n";

// The name and the slug diverge on this record, and the difference is the
// whole reason the path is built from the slug; show both so it is obvious.
echo "    name     ", $prisoner->name, "\n";
echo "    slug     ", $prisoner->slug, "   (photo path follows the slug, not the name)\n";

// The rights record is the point of the nonfree lane; check the credits row
// exists rather than trusting that it was added.
$credits = File::get(base_path("database/data/photos/CREDITS-nonfree.md"));
$credited = str_contains($credits, "`".$ph["file"]."`");

echo "\n    credited in CREDITS-nonfree.md: ", ($credited ? "yes" : "NO — the rights record is missing"), "\n";

echo "\n  ", wordwrap($payload["rationale"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["provenance"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["better_source_lead"], 72, "\n  "), "\n";

if ($onDisk && $credited && $prisoner->photo === $ph["to"]) { echo "\nB203-OK\n"; }
'

run_tinker "attach-king-photo" "B203-OK" "$ATTACH_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 203 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
