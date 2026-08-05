#!/usr/bin/env bash
#
# BATCH 181 -- Annie Adams: the confinement dates, the prison, and a
# photograph that needed reading before it could be used.
#
#   SHE WAS ALREADY HERE with three sentences, no dates, no prison, no
#   gender and no photograph. She was never charged with anything. She was
#   shut inside a penitentiary with her children so that her husband would
#   testify, and until now her record could not say for how long.
#
#   NOW IT CAN: March 3 to September 7, 1906, 188 days, at the Idaho State
#   Penitentiary in Boise. That institution already exists in this archive,
#   so it is matched rather than created a second time. Note it is NOT the
#   Ada County Jail where Haywood, Moyer and Pettibone were held — two
#   different Boise facilities, and this record belongs to the other one.
#
#   THE PHOTOGRAPH IS A NEWSPAPER CLIPPING WITH TWO WOMEN IN IT, and that
#   is the whole care of this batch. The supplied image is a half-page from
#   the Pittsburgh Press of June 19, 1907 headed "Love Breaks Double Bars
#   of Labor Prisoners at Boise". Its caption reads:
#
#     MRS. STEVE ADAMS ON RIGHT AND MRS. PETTIBONE ON LEFT, TALKING
#     THROUGH BARRED WINDOWS OF CELL TO PETTIBONE WITHIN
#
#   Dropped onto her profile whole, it would show Annie Adams and also
#   George Pettibone's wife, under a headline, with no indication which
#   was which. So the right-hand figure is cropped out and the caption
#   that identifies her is written into the record instead — the crop is
#   traceable because the sentence that justifies it is stored beside it.
#
#   AND THE PICTURE IS NOT OF HER CONFINEMENT. It is dated June 19, 1907,
#   nine months AFTER she was released. In it she is standing outside the
#   bars talking through them, a visitor during the Haywood trial, not a
#   prisoner. A photograph of a woman at a jail window on the record of a
#   woman jailed is exactly the kind of thing a reader will assume shows
#   the jailing, so the record says plainly that it does not.
#
#   NATIVE RESOLUTION, 182x314. The clipping scan is 568x739 and the
#   figure is a small part of it. Enlarging a 1907 halftone would add
#   size and no detail, so it goes in at the size it actually is.
#
#   Idempotent: the file is copied only when it differs, the dates are
#   fixed values, and the added paragraphs are keyed on a marker.
#
# Run from the repo root, after git pull (after batch 180):
#   bash database/data/run-batch-181.sh

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
echo "  Batch 181 — Annie Adams, 188 days and no charge"
echo "==================================================================="

SRC="database/data/files/adams/annie-adams.jpg"
DEST_DIR="storage/app/public/prisoners"

install_photo() {
    mkdir -p "$DEST_DIR"
    local dest="$DEST_DIR/annie-adams.jpg"

    [ -f "$SRC" ] || { echo "  missing source file: $SRC"; return 1; }

    if [ -f "$dest" ] && cmp -s "$SRC" "$dest"; then
        echo "  annie-adams.jpg — already installed, identical"
    else
        [ -f "$dest" ] && echo "  annie-adams.jpg — $(stat -c%s "$dest") bytes -> $(stat -c%s "$SRC")" \
                       || echo "  annie-adams.jpg — new file (the record had no photograph)"
        cp "$SRC" "$dest"
    fi

    [ -e "public/storage/prisoners/annie-adams.jpg" ] \
        || { echo "  !! not reachable through the public symlink — run php artisan storage:link"; return 1; }

    echo "  $(stat -c%s "$dest") bytes in place"
}

apply_record() {
    php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch181.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withUnderReview()->where("slug", $payload["slug"])->with("cases")->first();

if (! $p) { echo "  ", $payload["slug"], " not found — nothing changed.\n"; return; }

echo "  source (dates): ", $payload["source"]["dates"], "\n\n";

foreach ($payload["fields"] as $f => $v) { $p->{$f} = $v; }

if ($p->photo !== $payload["photo"]["path"]) { $p->photo = $payload["photo"]["path"]; }

if (mb_strpos($p->description ?? "", $payload["marker"]) === false) {
    $p->description = trim(($p->description ?? "").$payload["description_add"]);
}

$p->save();
$p->refresh()->load("cases");

$c = $payload["case"];

if ($p->cases->isEmpty()) { echo "  no case row — the dates have nowhere to go.\n"; return; }

$case = $p->cases->first();

// Already in this archive from the other Idaho records, so it is matched by
// name rather than created a second time.
$inst = Institution::firstOrCreate(
    ["name" => $c["institution_name"]],
    ["city" => $c["institution_city"], "state" => $c["institution_state"]],
);

echo "  institution: ", $inst->name, " (", ($inst->wasRecentlyCreated ? "created" : "existing"), ")",
    ($inst->city ? " — ".$inst->city : ""), ($inst->state ? ", ".$inst->state : ""), "\n";

$case->institution_id = $inst->id;
$case->incarceration_date = $c["incarceration_date"];
$case->release_date = $c["release_date"];
$case->sentence = $c["sentence"];
$case->save();
$case->refresh();

echo "\n  ", $p->name, "  [", $p->slug, "]\n";
echo "    aka        ", $p->aka, "\n";
echo "    gender     ", $p->gender, "\n";
echo "    confined   ", $case->formatPartialDate("incarceration_date"), "\n";
echo "    released   ", $case->formatPartialDate("release_date"), "\n";
echo "    days       ", ($case->imprisoned_for_days ?? "null"),
    "  (expected ", $c["expect_days"], ")",
    ((int) $case->imprisoned_for_days === (int) $c["expect_days"] ? "" : "   !! MISMATCH"), "\n";

$ok = $p->photo && Storage::disk("public")->exists($p->photo);

echo "    photo      ", $p->photo, "  ",
    ($ok ? Storage::disk("public")->size($p->photo)." bytes" : "MISSING ON DISK"), "\n";

echo "\n  the picture, and why it is cropped:\n";
echo "    ", wordwrap($payload["source"]["photo"], 70, "\n    "), "\n\n";
echo "    ", wordwrap($payload["photo"]["note"], 70, "\n    "), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "install-photo" install_photo
run "apply-record"  apply_record

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 181 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "188 days, no charge, and a face. The photograph is nine months after"
echo "her release and the record says so, because a woman at a jail window"
echo "on a jailed womans profile will otherwise be read as the jailing."
