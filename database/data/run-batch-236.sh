#!/usr/bin/env bash
#
# BATCH 236 -- eight calendar entries get a picture.
#
#   SEVEN WERE BLANK AND ONE WAS WRONG. Of 158 published calendar entries,
#   73 carry no image of their own; 35 of those are covered by the linked
#   prisoner photograph and 38 render with nothing at all. Seven of the 38
#   are filled here.
#
#   THE MARIN ENTRY IS THE ONE THAT WAS WRONG. It carried a photograph of
#   two people at a Save the Soledad Brothers picket -- related, but not
#   the courthouse. It is replaced with the photograph of the escape
#   itself: James McClain leading the hostages out with a pistol extended,
#   Judge Harold Haley in his judicial robe with a sawed-off shotgun taped
#   under his chin, Ruchell Magee at the right. Roger Bockrath, front page
#   of the Daily Independent Journal, 7 August 1970. The picket photograph
#   is detached, not deleted -- the file stays on disk.
#
#   THE BLANKS WERE NOT ALL NEGLECT. The July 2026 audit recorded in
#   calendar-fixes/CREDITS.md cleared several of them deliberately rather
#   than leave a wrong picture up: a maile vine was illustrating the arrest
#   of Maile Hampton, an Anubis statue was standing in for Bunchy Carter
#   and John Huggins, and six more pointed at files that had gone missing.
#   Four of the entries filled here are on that audit list of gaps it could
#   not close.
#
#   RIGHTS ARE SPLIT AND THE SPLIT IS KEPT. Five come from the Library of
#   Congress under No known restrictions on publication, and one is a
#   Wikimedia Commons public domain file. Two are from the New York
#   World-Telegram and Sun collection, which carries a publication advisory
#   rather than a clearance, so they are staged under nonfree and credited
#   separately -- the same call made for Carolyn Hart and Bruno Grunzig.
#
#   THREE GAPS ARE LEFT OPEN ON PURPOSE, and the reasons are in the
#   credits. The free Selma photographs are of the successful march of 21
#   to 25 March, not of the beating on the 7th. The free Greensboro image
#   is a 2017 photograph of the preserved lunch counter. And the FBI
#   missing poster, the obvious picture for the recovery of Chaney,
#   Goodman and Schwerner, is already used on 22 June for the day they
#   disappeared.
#
#   Idempotent: files copied only when absent or different, image column
#   written only when it differs.
#
# Run from the repo root, after git pull, after batch 235:
#   bash database/data/run-batch-236.sh

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
echo "  Batch 236 — calendar images"
echo "==================================================================="

SRC="database/data/photos/calendar-fixes"
DEST="storage/app/public/calendar"

echo
echo "--- install-images"
install_ok=1
mkdir -p "$DEST"
for f in suffrage-parade-attacked-in-washington \
         supreme-court-upholds-debs-conviction \
         haymarket-bombing-and-police-riot-in-chicago \
         industrial-workers-of-the-world-founded-in-chicago \
         elaine-massacre-begins-in-arkansas \
         marin-county-courthouse-incident-kills-jonathan-jackson; do
    src="$SRC/$f.jpg"
    dest="$DEST/$f.jpg"
    if [ ! -f "$src" ]; then
        echo "  missing source: $src"; install_ok=0; continue
    fi
    if [ -f "$dest" ] && cmp -s "$src" "$dest"; then
        echo "  $f.jpg — already installed, identical"
    else
        cp "$src" "$dest"
        echo "  $f.jpg — $(stat -c%s "$dest") bytes installed"
    fi
done

for f in sacco-and-vanzetti-executed \
         iww-general-strike-against-sacco-vanzetti-executions; do
    src="$SRC/nonfree/$f.jpg"
    dest="$DEST/$f.jpg"
    if [ ! -f "$src" ]; then
        echo "  missing source: $src"; install_ok=0; continue
    fi
    if [ -f "$dest" ] && cmp -s "$src" "$dest"; then
        echo "  $f.jpg — already installed, identical (nonfree)"
    else
        cp "$src" "$dest"
        echo "  $f.jpg — $(stat -c%s "$dest") bytes installed (nonfree)"
    fi
done

if [ ! -e "public/storage/calendar" ]; then
    echo "  !! storage/calendar not reachable through the public symlink — run php artisan storage:link"
    install_ok=0
fi
[ "$install_ok" -eq 1 ] || FAILED+=("install-images")

ATTACH_CODE='
use App\Models\CalendarEntry;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch236.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$bad = [];
$done = 0;

echo "\n";

foreach ($payload["entries"] as $e) {
    $entry = CalendarEntry::where("month", $e["month"])->where("day", $e["day"])
        ->where("title", $e["expect_title"])->first();

    if (! $entry) {
        echo "  !! no entry titled ", $e["expect_title"], " on ", $e["month"], "/", $e["day"], "\n";
        $bad[] = $e["file"];

        continue;
    }

    $path = "calendar/".$e["file"];

    if (! Storage::disk("public")->exists($path)) {
        echo "  !! not on disk: ", $path, "\n";
        $bad[] = $e["file"];

        continue;
    }

    $was = $entry->image ?: "(none)";

    // The one replacement is announced rather than slipped in.
    if ($entry->image && $entry->image !== $path) {
        echo "  REPLACING on ", $e["month"], "/", $e["day"], ": ", $was, "\n";
    }

    if ($entry->image !== $path) {
        $entry->image = $path;
        $entry->save();
    }

    $entry->refresh();
    $done++;

    echo "  ", str_pad($e["month"]."/".$e["day"], 6), " ", str_pad($entry->title, 52), " ",
        number_format(Storage::disk("public")->size($path) / 1024, 1), " KB\n";
    echo "         ", $e["note"], "\n";
}

// Nothing else on the calendar should have moved.
$total = CalendarEntry::where("published", true)->count();
$withImage = CalendarEntry::where("published", true)->whereNotNull("image")->where("image", "!=", "")->count();

echo "\n  published entries          ", $total, "\n";
echo "  carrying an image          ", $withImage, "\n";
echo "  still without one          ", $total - $withImage, "\n";

// Files referenced but not present — the fault this batch did not create
// and does not fix, surfaced so it stays visible.
$missing = [];

foreach (CalendarEntry::where("published", true)->whereNotNull("image")->get() as $c) {
    if ($c->image && ! Storage::disk("public")->exists($c->image)) {
        $missing[] = $c->month."/".$c->day." ".$c->title." -> ".$c->image;
    }
}

echo "\n  entries whose image file is missing from disk: ", count($missing), "\n";

foreach ($missing as $m) { echo "    !! ", $m, "\n"; }

echo "\n  ", wordwrap($payload["replacement"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["why_blank"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["rights"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["not_filled"], 72, "\n  "), "\n";

echo "\n  problems: ", count($bad), "\n";

foreach ($bad as $b) { echo "    !! ", $b, "\n"; }

if (count($bad) === 0 && $done === (int) $payload["expected"]["installed"]) { echo "\nB236-OK\n"; }
'

run_tinker "attach-calendar-images" "B236-OK" "$ATTACH_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 236 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
