#!/usr/bin/env bash
#
# BATCH 205 -- Elizabeth Packard's portrait replaced with the National
# Women's History Museum scan.
#
#   THE SUPPLIED LINK WAS A THUMBNAIL. It points at a Drupal image-style
#   derivative -- the /styles/main_image/ path -- which serves the
#   picture at 300x300. Stripping that path gives the original upload at
#   600x600: same photograph, same crop, twice the resolution each way.
#   So this is the supplied image, not a substitute for it.
#
#   IT REPLACES the Wikimedia Commons portrait File:Epwpackard.jpg at
#   200x392 that batch 202 installs. Same underlying nineteenth-century
#   photograph -- side by side it is unmistakably the same sitting -- but
#   the Commons copy is a taller, narrower crop with 200 pixels across
#   against 600 here. Three times the width on her face, and square,
#   which is the shape this site renders portraits in.
#
#   STILL PUBLIC DOMAIN, and still in the free folder rather than
#   photos/nonfree/. The underlying portrait is a mid-nineteenth-century
#   photograph of a woman who died in 1897, so it is out of copyright by
#   age, and a faithful reproduction of a flat public-domain work
#   attracts no new copyright of its own in the United States. The museum
#   is credited as the source of this scan as a courtesy, not because it
#   holds a right in it.
#
#   INSTALLED UNMODIFIED. It is grainy at full size -- an old halftone
#   scan, and no amount of resolution fixes that. No sharpening, no
#   denoise, no crop: retouching a nineteenth-century scan to look
#   cleaner than it is would be inventing detail.
#
#   ORDER DOES NOT MATTER. The repository file is replaced, so a first
#   run of 202 installs this image directly; if 202 already ran with the
#   Commons copy, this overwrites it on disk and leaves the photo field
#   untouched, because the path does not change.
#
#   Idempotent: the file is copied only when it differs.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-205.sh

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
echo "  Batch 205 — Elizabeth Packard, replacement portrait"
echo "==================================================================="

SRC="database/data/files/prisoners/elizabeth-packard.jpg"
DEST_DIR="storage/app/public/prisoners"

echo
echo "--- install-photo"
install_ok=1
mkdir -p "$DEST_DIR"
if [ ! -f "$SRC" ]; then
    echo "  missing source file: $SRC"; install_ok=0
else
    dest="$DEST_DIR/elizabeth-packard.jpg"
    if [ -f "$dest" ] && cmp -s "$SRC" "$dest"; then
        echo "  elizabeth-packard.jpg — already the new scan, identical"
    else
        [ -f "$dest" ] && echo "  elizabeth-packard.jpg — $(stat -c%s "$dest") bytes -> $(stat -c%s "$SRC")" \
                       || echo "  elizabeth-packard.jpg — new file"
        cp "$SRC" "$dest"
    fi
    if [ ! -e "public/storage/prisoners/elizabeth-packard.jpg" ]; then
        echo "  !! not reachable through the public symlink — run php artisan storage:link"
        install_ok=0
    fi
fi
[ "$install_ok" -eq 1 ] || FAILED+=("install-photo")

CHECK_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch205.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$ph = $payload["photo"];

$prisoner = Prisoner::withoutGlobalScopes()->where("slug", $ph["slug"])->first();

if (! $prisoner) {
    echo "  no prisoner at slug ", $ph["slug"], " — run batch 202 first. The file is installed and waiting.\n";

    return;
}

if (! Storage::disk("public")->exists($ph["to"])) {
    echo "  !! photo not on disk at ", $ph["to"], " — the install step above did not land.\n";

    return;
}

// The path does not change, so usually there is nothing to write here; the
// field is set only if 202 has not run or pointed somewhere else.
if ($prisoner->photo !== $ph["to"]) {
    $prisoner->photo = $ph["to"];
    $prisoner->save();
    $prisoner->refresh();
    echo "  photo field set to ", $prisoner->photo, "\n";
} else {
    echo "  photo field already ", $prisoner->photo, " — only the file behind it changed.\n";
}

$bytes = Storage::disk("public")->size($prisoner->photo);
$dims = @getimagesize(Storage::disk("public")->path($prisoner->photo));

echo "\n  ", $prisoner->name, "  [/prisoner/", $prisoner->slug, "]\n";
echo "    photo   ", $prisoner->photo, "\n";
echo "    size    ", ($dims ? $dims[0]."x".$dims[1] : "unknown"), "   ", $bytes, " bytes\n";
echo "    url     ", $prisoner->photoUrl(), "\n";
echo "    source  ", $ph["used_url"], "\n";

$big = $dims && $dims[0] >= 600 && $dims[1] >= 600;

echo "\n    the 600x600 scan is in place, not the 200x392 Commons copy: ", ($big ? "yes" : "NO"), "\n";

echo "\n  ", wordwrap($payload["why_a_different_url"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["rights"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["quality_note"], 72, "\n  "), "\n";

if ($big && $prisoner->photo === $ph["to"]) { echo "\nB205-OK\n"; }
'

run_tinker "replace-portrait" "B205-OK" "$CHECK_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 205 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
