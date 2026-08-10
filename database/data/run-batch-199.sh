#!/usr/bin/env bash
#
# BATCH 199 -- Bronner: the curator's biography, and the top of the
# photograph cropped.
#
#   THE PHOTOGRAPH had 128 pixels of sky and awning above his hair, 21
#   percent of a 410x600 frame, which reads as a mis-framed snapshot at
#   the sizes this site renders portraits. Cropped at y=85 to 410x515,
#   leaving about 8 percent headroom -- the ordinary range for a portrait
#   -- and keeping the All-One slogan on his sweatshirt in frame. Nothing
#   else about the photograph is altered. The file in the repository is
#   the cropped one now, so batch 198 installs the same image if it has
#   not run yet.
#
#   THE BIOGRAPHY IS THE CURATORS, with one character changed: Broner in
#   the last sentence is written Bronner. A man misspelled in his own
#   biography is worse than an unannounced correction, and the surname is
#   spelled correctly three times in the same paragraph.
#
#   THE CASE ROW CHANGES WITH IT, because the new text contradicts what
#   the record asserted. Batch 198 said he was never charged with a crime
#   -- written from accounts that describe the commitment and not what
#   came before it. The curators text says he was arrested for
#   trespassing at the University of Chicago and posted bail, which is a
#   charge. charges and convicted are rewritten to match: arrested and
#   bailed on trespassing, never tried, then held by civil commitment
#   instead. Leaving 198s wording beside this biography would have the
#   page contradict itself.
#
#   STILL NO ARREST DATE, but for a different reason than in 198. That
#   batch left it empty because a civil commitment is not an arrest.
#   There was an arrest -- the trespassing one -- and its date is simply
#   not known, so the field stays empty rather than being guessed.
#
#   THE ESCAPE IS NOW STATED OUTRIGHT in the biography, settling what 198
#   flagged. release_date stays 1947-07-07: the schema has no
#   escaped_date, and that is still the day the confinement ended.
#
#   ELGIN STATE HOSPITAL is left as the institution name, which is what
#   the place was called. The curators prose says Elgin State Mental
#   Hospital and stays exactly as written -- same hospital, and the
#   biography is a voice, not a field to normalise.
#
#   Idempotent: the file is copied only when it differs, fields written
#   only when they differ.
#
# Run from the repo root, after git pull, after batch 198:
#   bash database/data/run-batch-199.sh

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
echo "  Batch 199 — Bronner: biography and cropped photograph"
echo "==================================================================="

SRC="database/data/files/prisoners/emanuel-bronner.jpg"
DEST_DIR="storage/app/public/prisoners"

echo
echo "--- install-cropped-photo"
install_ok=1
mkdir -p "$DEST_DIR"
if [ ! -f "$SRC" ]; then
    echo "  missing source file: $SRC"; install_ok=0
else
    dest="$DEST_DIR/emanuel-bronner.jpg"
    if [ -f "$dest" ] && cmp -s "$SRC" "$dest"; then
        echo "  emanuel-bronner.jpg — already the cropped file, identical"
    else
        [ -f "$dest" ] && echo "  emanuel-bronner.jpg — $(stat -c%s "$dest") bytes -> $(stat -c%s "$SRC")" \
                       || echo "  emanuel-bronner.jpg — new file"
        cp "$SRC" "$dest"
    fi
    if [ ! -e "public/storage/prisoners/emanuel-bronner.jpg" ]; then
        echo "  !! not reachable through the public symlink — run php artisan storage:link"
        install_ok=0
    fi
fi
[ "$install_ok" -eq 1 ] || FAILED+=("install-cropped-photo")

UPDATE_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch199.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = $payload["prisoner"];

$prisoner = Prisoner::withoutGlobalScopes()->where("slug", $p["slug"])->first();

if (! $prisoner) {
    echo "  no prisoner at slug ", $p["slug"], " — run batch 198 first. Nothing changed.\n";

    return;
}

$changed = [];

if ($prisoner->description !== $p["description"]) {
    $prisoner->description = $p["description"];
    $changed[] = "description";
}

// The photo path may already be right from 198; the file behind it is what
// this batch replaces.
$want = "prisoners/".$prisoner->slug.".jpg";

if ($prisoner->photo !== $want) { $prisoner->photo = $want; $changed[] = "photo"; }

if ($changed) { $prisoner->save(); }

$prisoner->refresh();
$prisoner->load("cases.institution");

$case = $prisoner->cases->first();
$caseChanged = [];

if ($case) {
    foreach ($payload["case"] as $k => $v) {
        if ($case->{$k} !== $v) { $case->{$k} = $v; $caseChanged[] = $k; }
    }

    if ($caseChanged) { $case->save(); $case->refresh(); }
}

$onDisk = $prisoner->photo && Storage::disk("public")->exists($prisoner->photo);
$bytes = $onDisk ? Storage::disk("public")->size($prisoner->photo) : 0;
$words = str_word_count(strip_tags((string) $prisoner->description));

echo "\n  ", $prisoner->name, "  [/prisoner/", $prisoner->slug, "]\n";
echo "    prisoner fields set: ", ($changed ? implode(", ", $changed) : "nothing — already correct"), "\n";
echo "    case fields set:     ", ($caseChanged ? implode(", ", $caseChanged) : "nothing — already correct"), "\n";
echo "    description words:   ", $words, "   (expected ", $payload["expected"]["description_words"], ")\n";
echo "    photo:               ", $prisoner->photo, "  ", ($onDisk ? $bytes." bytes on disk" : "MISSING ON DISK"), "\n";

if ($case) {
    echo "    institution:         ", $case->institution?->name, " — ", $case->institution?->city, ", ", $case->institution?->state, "\n";
    echo "    confined / out:      ", $case->incarceration_date?->toDateString(), "  ", $case->release_date?->toDateString(), "\n";
    echo "    days:                ", $case->imprisoned_for_days, "   (expected ", $payload["expected"]["imprisoned_for_days"], ")\n";
    echo "    convicted:           ", $case->convicted, "\n";
}

// The whole point of the case rewrite is that the page must not contradict
// itself, so check the old claim is actually gone.
$stale = $case && mb_stripos((string) $case->charges, "never charged with a crime") !== false;

echo "\n    the superseded never-charged wording is gone: ", ($stale ? "NO — still present" : "yes"), "\n";
echo "    biography says he escaped: ",
    (mb_stripos((string) $prisoner->description, "escaped from the hospital") !== false ? "yes" : "NO"), "\n";
echo "    surname spelled Broner anywhere in the biography: ",
    (mb_strpos((string) $prisoner->description, "Broner ") !== false ? "YES — the typo came back" : "no"), "\n";

echo "\n  ", wordwrap($payload["photo"]["crop"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["case_note"], 72, "\n  "), "\n";

$ok = $onDisk
    && ! $stale
    && $words === (int) $payload["expected"]["description_words"]
    && mb_strpos((string) $prisoner->description, "Broner ") === false;

if ($ok) { echo "\nB199-OK\n"; }
'

run_tinker "update-bronner" "B199-OK" "$UPDATE_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 199 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
