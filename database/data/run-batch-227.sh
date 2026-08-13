#!/usr/bin/env bash
#
# BATCH 227 -- the three Woodlawn sedition defendants get their faces.
#
#   PETE MUSELIN, MILAN RESETAR AND TOM ZIMA were all already here, all
#   three convicted under the Pennsylvania sedition law and sent to
#   Blawnox, and not one of them had a photograph.
#
#   ONE PAGE, THREE PORTRAITS. An International Labor Defense pamphlet,
#   SEDITION -- To Protest Against Unemployment, page 16, which prints the
#   three men one above the other with a caption under each.
#
#   THE IDENTIFICATION IS THE PRINTING, NOT A GUESS. Each crop is taken
#   from the position its own caption sits beneath: Muselin in the oval
#   vignette at the top, Resetar in the lower-left frame, Zima in the
#   lower-right. Nothing here was matched on name similarity. The check
#   that the page and the records are about the same three men is that
#   Muselin own description in this database already names Zima and
#   Resetar as his codefendants.
#
#   THE PAMPHLET PRINTS PETER, THE RECORD SAYS PETE. The record is left
#   as it is -- the difference is not evidence of a different man, and
#   renaming records to whatever a source happens to print has gone wrong
#   here before.
#
#   NOT UPSCALED. The crops stay at the resolution of the scan, 152x184 to
#   213x203. The halftone screen of the original printing is visible at
#   this size; enlarging would add dots, not detail.
#
#   RIGHTS. An early-1930s ILD pamphlet, almost certainly public domain by
#   age and non-renewal, so these go in the open photos folder rather than
#   nonfree. Credited in CREDITS-woodlawn-sedition.md, with the two
#   caveats kept rather than glossed: the page carries no date, and the
#   scan is a WordPress upload rather than a library copy.
#
#   Idempotent: files copied only when absent or different, photo field
#   written only when it differs.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-227.sh

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
echo "  Batch 227 — Woodlawn sedition defendants, portraits"
echo "==================================================================="

SRC_DIR="database/data/photos"
DEST_DIR="storage/app/public/prisoners"

echo
echo "--- install-photos"
install_ok=1
mkdir -p "$DEST_DIR"
for f in pete-muselin milan-resetar tom-zima; do
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

$payload = json_decode(File::get(base_path("database/data/fixes/batch227.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$credits = File::get(base_path("database/data/photos/CREDITS-woodlawn-sedition.md"));
$bad = [];

echo "\n";

foreach ($payload["photos"] as $ph) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $ph["slug"])->first();

    if (! $p) { echo "  !! no prisoner at slug ", $ph["slug"], "\n"; $bad[] = $ph["slug"]; continue; }

    // The slug must still hold the person this crop was taken for.
    if ($p->name !== $ph["expect_name"]) {
        echo "  !! ", $ph["slug"], " holds ", $p->name, ", not ", $ph["expect_name"], " — skipping\n";
        $bad[] = $ph["slug"];

        continue;
    }

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

    echo "  ", str_pad($p->name, 16), " ", str_pad($was, 8), " -> ", str_pad($p->photo, 30),
        str_pad(number_format($bytes / 1024, 1)." KB", 10),
        ($credited ? "credited" : "NOT CREDITED"), "\n";
    echo "  ", str_pad("", 16), " ", $ph["position"], "\n";
}

$slugs = array_column($payload["photos"], "slug");
$done = Prisoner::withoutGlobalScopes()->whereIn("slug", $slugs)
    ->get()->filter(fn ($p) => filled($p->photo))->count();

echo "\n  ", $done, " of ", count($slugs), " now carry a photograph\n";

echo "\n  ", wordwrap($payload["identification"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["rights"], 72, "\n  "), "\n";

if (count($bad) === 0 && $done === count($slugs)) { echo "\nB227-OK\n"; }
'

run_tinker "attach-photos" "B227-OK" "$ATTACH_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 227 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
