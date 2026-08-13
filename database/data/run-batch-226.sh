#!/usr/bin/env bash
#
# BATCH 226 -- the photograph on the Frances Hart record is not her.
#
#   IT IS A LIVING PERSON. A 2400x3000, 5.4 MB colour studio portrait, on
#   a record whose subject was sentenced to four years and taken into San
#   Quentin on 12 July 1923. Not a scan of a period photograph; a modern
#   photograph of somebody else.
#
#   EVERYONE ELSE IN THE SAME COHORT IS FINE, which is what makes this an
#   isolated fault rather than a bad import. The other San Quentin IWW
#   records carry exactly what they should -- sepia mugshots with the
#   prison number inked on the chest, John Mitchell 4827, John Downs 4843,
#   Roy Davis 4884, consecutive numbers from the same series. A sweep of
#   all 597 records in eras up to the 1920s found nine carrying colour
#   images; the ones opened turned out to be sepia archival scans, which
#   register as colour and are genuine. This one is not.
#
#   CLEARED AND MOVED OUT OF THE PUBLIC DISK. Clearing the field alone
#   would stop the page showing it but leave it served at
#   /storage/prisoners/frances-hart.jpg to anyone with the URL, so the
#   file goes to storage/app/removed-photos, which is outside the public
#   disk. Moved rather than deleted: reversible if it turns out to belong
#   on some other record, and whoever is in it should not be published on
#   a stranger record in the meantime.
#
#   THREE THINGS ARE FLAGGED AND LEFT ALONE, because none can be settled
#   from the record and nothing found online documents this person at all:
#
#     the description says "he served" and "his term expired" while
#     Gender is Female, so one of the two is wrong, and the name may be
#     Francis;
#
#     the era is 1910s while the incarceration date is 1923;
#
#     the charge reads as a federal Espionage Act prosecution while the
#     institution is San Quentin, a California state prison, in the year
#     of the state criminal syndicalism cases.
#
#   Idempotent: the field is cleared only if set, the file moved only if
#   still there.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-226.sh

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
echo "  Batch 226 — Frances Hart, wrong photograph removed"
echo "==================================================================="

SRC="storage/app/public/prisoners/frances-hart.jpg"
DEST_DIR="storage/app/removed-photos"

echo
echo "--- quarantine-file"
mkdir -p "$DEST_DIR"
if [ -f "$SRC" ]; then
    dest="$DEST_DIR/frances-hart-not-this-person.jpg"
    mv "$SRC" "$dest"
    echo "  moved $(stat -c%s "$dest") bytes to $dest"
    echo "  no longer served from the public disk"
elif [ -f "$DEST_DIR/frances-hart-not-this-person.jpg" ]; then
    echo "  already quarantined — nothing to move"
else
    echo "  no file at $SRC — nothing to move"
fi

CLEAR_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch226.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withoutGlobalScopes()->where("slug", $payload["prisoner"]["slug"])->first();

if (! $p) { echo "  !! no prisoner at that slug\n"; return; }

if ($p->name !== $payload["prisoner"]["expect_name"]) {
    echo "  !! that slug holds ", $p->name, " — stopping.\n";

    return;
}

$was = $p->photo ?: "(none)";

if ($p->photo !== null) {
    $p->photo = null;
    $p->save();
    $p->refresh();
}

echo "\n  ", $p->name, "  [/prisoner/", $p->slug, "]\n";
echo "    photo        ", $was, "  ->  ", ($p->photo ?: "(none)"), "\n";
echo "    still on the public disk: ",
    (Storage::disk("public")->exists($payload["photo"]["file"]) ? "YES — the move above did not happen" : "no"), "\n";

// The rest of the record is untouched and is printed so the flagged
// contradictions are visible rather than described.
$case = $p->cases()->with("institution")->first();

echo "\n  untouched, and flagged:\n";
echo "    gender       ", $p->gender, "\n";
echo "    description  ", $p->description, "\n";
echo "    era          ", $p->era, "   (incarceration is 1923)\n";

if ($case) {
    echo "    institution  ", ($case->institution?->name ?: "(none)"), "\n";
    echo "    incarcerated ", optional($case->incarceration_date)->toDateString(), "\n";
    echo "    charges      ", (is_array($case->charges) ? implode("; ", $case->charges) : (string) $case->charges), "\n";
}

echo "\n  ", wordwrap($payload["evidence"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["not_changed"], 72, "\n  "), "\n";

$ok = $p->photo === null && ! Storage::disk("public")->exists($payload["photo"]["file"]);

if ($ok) { echo "\nB226-OK\n"; }
'

run_tinker "clear-photo" "B226-OK" "$CLEAR_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 226 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
