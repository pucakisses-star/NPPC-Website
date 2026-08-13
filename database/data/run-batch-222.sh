#!/usr/bin/env bash
#
# BATCH 222 -- nine portraits, from two captioned group images.
#
#   THIRTY-FOUR PEOPLE touched by the recent Panther work have no
#   photograph. This is nine of them, off two files on
#   itsabouttimebpp.com. Both are image-only PDFs with no text layer, so
#   the pages were rendered and the panels cropped, and the captions were
#   read off the rendered page rather than extracted.
#
#   NEITHER SET WAS MATCHED BY NAME SIMILARITY. That matters, because on
#   this archive a name search for James White once returned a leader of
#   Shays Rebellion.
#
#   The San Quentin Six grid is captioned clockwise from upper left, and
#   that reading is ANCHORED: the upper-left panel is the same photograph
#   as the Fleeta Drumgo portrait already in this archive. So the other
#   five positions follow from a checked starting point instead of an
#   assumption about how a caption is meant to be read.
#
#   The Panther 21 page carries a name printed under each face, and each
#   was matched to a record through that record own alias or description
#   field: EDWARD JOSEPH to Jamal Joseph because his description gives his
#   birth name as Eddie Joseph; ALIBY HASSAN to Ali Bey Hassan. Column
#   boundaries were found by detecting the white gutters between cells,
#   not by eye.
#
#   THREE FACES WERE LEFT ON THE PAGE. ALEX McKIEVER is very likely
#   Abayama Katara and WALTER JOHNSON very likely Baba Odinga, but neither
#   record carries an alias to justify it, and KENNSIE SOANES cannot be
#   Kwando Kinshasa, whose record gives his birth name as William King.
#   Attaching a face on an alias the archive has never recorded is exactly
#   how a photograph ends up on the wrong person.
#
#   FOUR PEOPLE ON THAT PAGE ARE NOT IN THIS DATABASE AT ALL: ALBERT
#   NIEVES, ROSELAND BENNITT, SHARON WILLIAMS and RAYMOND QUINONES. The
#   page also shows nineteen Panther 21 records here against twenty-one
#   defendants.
#
#   AND TWO RECORDS CONTRADICT EACH OTHER: Abayama Katara is described as
#   the youngest of the defendants, a teenager, and Jamal Joseph as at age
#   16 the youngest defendant. Both cannot be right. Neither is touched.
#
#   RESOLUTION IS HONEST. The Panther 21 faces are about 70 by 120 pixels
#   in the source strip and cannot be had larger; they are enlarged twice,
#   which adds no detail but puts them at the scale of the portraits
#   already here. Every file is credited in CREDITS-nonfree.md.
#
#   Idempotent: files copied only when absent or different, photo field
#   written only when it differs.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-222.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

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
echo "  Batch 222 — nine portraits for people who had none"
echo "==================================================================="

SRC_DIR="database/data/photos/nonfree"
DEST_DIR="storage/app/public/prisoners"

echo
echo "--- install-photos"
install_ok=1
mkdir -p "$DEST_DIR"
for f in luis-talamantez willie-tate david-johnson johnny-spain \
         lumumba-shakur ali-bey-hassan jamal-joseph joan-bird robert-collier; do
    src="$SRC_DIR/$f.jpg"
    dest="$DEST_DIR/$f.jpg"
    if [ ! -f "$src" ]; then
        echo "  missing source: $src"; install_ok=0; continue
    fi
    if [ -f "$dest" ] && cmp -s "$src" "$dest"; then
        echo "  $f.jpg — already installed, identical"
    else
        cp "$src" "$dest"
        echo "  $f.jpg — $(stat -c%s "$dest") bytes installed"
    fi
    if [ ! -e "public/storage/prisoners/$f.jpg" ]; then
        echo "  !! not reachable through the public symlink — run php artisan storage:link"
        install_ok=0
    fi
done
[ "$install_ok" -eq 1 ] || FAILED+=("install-photos")

ATTACH_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch222.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$credits = File::get(base_path("database/data/photos/CREDITS-nonfree.md"));
$bad = [];

echo "\n";

foreach ($payload["photos"] as $ph) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $ph["slug"])->first();

    if (! $p) { echo "  !! no prisoner at slug ", $ph["slug"], "\n"; $bad[] = $ph["slug"]; continue; }

    if (! Storage::disk("public")->exists($ph["to"])) {
        echo "  !! not on disk: ", $ph["to"], "\n"; $bad[] = $ph["slug"]; continue;
    }

    $was = $p->photo ?: "(none)";

    if ($p->photo !== $ph["to"]) {
        $p->photo = $ph["to"];
        $p->save();
        $p->refresh();
    }

    $bytes = Storage::disk("public")->size($p->photo);
    $credited = str_contains($credits, "`".$ph["file"]."`");

    if (! $credited) { $bad[] = $ph["slug"]." not credited"; }

    echo "  ", str_pad($p->name, 22), " ", str_pad($was, 8), " -> ", str_pad($p->photo, 34),
        str_pad(number_format($bytes / 1024, 1)." KB", 10),
        ($credited ? "credited" : "NOT CREDITED"), "\n";
    echo "  ", str_pad("", 22), " ", $ph["source"], "\n";
}

// How the photo gap stands now across the people this run has been about.
$slugs = array_column($payload["photos"], "slug");
$done = Prisoner::withoutGlobalScopes()->whereIn("slug", $slugs)
    ->get()->filter(fn ($p) => filled($p->photo))->count();

echo "\n  ", $done, " of ", count($slugs), " now carry a photograph\n";

echo "\n  ", wordwrap($payload["identification_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["not_taken"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["people_absent"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["contradiction"], 72, "\n  "), "\n";

if (count($bad) === 0 && $done === count($slugs)) { echo "\nB222-OK\n"; }
'

run_tinker "attach-photos" "B222-OK" "$ATTACH_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 222 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
