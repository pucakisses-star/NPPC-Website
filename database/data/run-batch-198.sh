#!/usr/bin/env bash
#
# BATCH 198 -- Emanuel Theodore Bronner, Elgin State Hospital, 1946-47.
#
#   HE BELONGS HERE FOR THE REASON THE ARCHIVE EXISTS. Bronner was locked
#   up for fifteen months over what he believed and said, by a mechanism
#   that required no charge, no trial and no sentence. The absence of a
#   prosecution is the point of the record, not a hole in it. Seventy-one
#   records in this database already describe psychiatric confinement in
#   one form or another.
#
#   THE MAN: born Emanuel Heilbronner in Heilbronn in 1908 into a Jewish
#   family of master soapmakers; emigrated in 1929; cut the Heil from the
#   family name after Hitler came to power; the relatives who stayed were
#   murdered in the Holocaust. Committed to Elgin State Hospital on March
#   27, 1946 while lecturing on the one-world creed he called the Moral
#   ABC. Out by July 7, 1947, and afterwards founded Dr. Bronners Magic
#   Soaps and printed that creed on every bottle -- which is how a
#   philosophy written under confinement ended up in millions of American
#   bathrooms.
#
#   THE ESCAPE, FLAGGED NOT SMOOTHED. Most published accounts describe
#   Bronner ESCAPING from Elgin rather than being discharged. The
#   curators date is stored in release_date because that is the field the
#   schema has and July 7, 1947 is when the confinement ended either way,
#   but the description says plainly that accounts describe an escape.
#   There is no escaped_date column; if the record should assert it more
#   strongly that is a one-field edit.
#
#   NO ARREST DATE, DELIBERATELY. A civil commitment is not an arrest.
#   Inventing one would imply a criminal process that never happened and
#   would drop him into the wrong cohort for sort-order placement, which
#   keys on arrest dates first. Without one the placement falls back to
#   the era and end-of-run tiers, which is right for him.
#
#   DURATION IS COMPUTED, NOT TYPED. imprisoned_for_days is left to
#   PrisonerCase to derive from the two dates -- 467 days -- so the
#   record and the arithmetic cannot drift apart later.
#
#   THE PHOTOGRAPH is the curators, from the Immigrant Entrepreneurship
#   project: Bronner in later life, blind, in dark glasses and a
#   sweatshirt carrying his own All-One slogan. Installed unmodified at
#   410x600. It is already a clean portrait, and cropping a
#   curator-supplied photograph without a reason to is interference
#   rather than curation.
#
#   WHAT IS SOURCED FROM WHERE: the two dates and the photograph are the
#   curators. The biography is written from published accounts and is
#   hedged where those accounts vary -- the electroshock and the escape
#   are attributed, not asserted flatly.
#
#   Idempotent: prisoner:add refuses on a duplicate name, and the photo
#   step is safe to repeat.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-198.sh

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
echo "  Batch 198 — Emanuel Theodore Bronner"
echo "==================================================================="

SRC="database/data/files/prisoners/emanuel-bronner.jpg"
DEST_DIR="storage/app/public/prisoners"

echo
echo "--- install-photo"
install_ok=1
mkdir -p "$DEST_DIR"
if [ ! -f "$SRC" ]; then
    echo "  missing source file: $SRC"; install_ok=0
else
    dest="$DEST_DIR/emanuel-bronner.jpg"
    if [ -f "$dest" ] && cmp -s "$SRC" "$dest"; then
        echo "  emanuel-bronner.jpg — already installed, identical"
    else
        cp "$SRC" "$dest"
        echo "  emanuel-bronner.jpg — $(stat -c%s "$dest") bytes installed"
    fi
    if [ ! -e "public/storage/prisoners/emanuel-bronner.jpg" ]; then
        echo "  !! not reachable through the public symlink — run php artisan storage:link"
        install_ok=0
    fi
fi
[ "$install_ok" -eq 1 ] || FAILED+=("install-photo")

ADD_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch198.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = $payload["prisoner"];

$existing = Prisoner::withoutGlobalScopes()->where("name", $p["name"])->first();

if ($existing) {
    echo "  ", $p["name"], " already exists [", $existing->slug, "] — not created again.\n";
    $prisoner = $existing;
} else {
    // The artisan command is the supported path: it enforces the duplicate
    // guard, creates the institution, and places the sort_order beside peers
    // instead of leaving it at 0 in front of the whole database.
    Artisan::call("prisoner:add", ["json" => json_encode($p)]);
    echo Artisan::output();

    $prisoner = Prisoner::withoutGlobalScopes()->where("name", $p["name"])->first();
}

if (! $prisoner) { echo "  !! prisoner was not created — stopping.\n"; return; }

// The file is installed under the expected slug; copy it to whatever slug was
// actually generated rather than assuming the two match.
$want = "prisoners/".$prisoner->slug.".jpg";
$installed = "prisoners/".$payload["photo"]["file"];

if ($want !== $installed && Storage::disk("public")->exists($installed)) {
    Storage::disk("public")->put($want, Storage::disk("public")->get($installed));
    echo "  slug is ", $prisoner->slug, " — photo copied to ", $want, "\n";
}

if ($prisoner->photo !== $want) { $prisoner->photo = $want; $prisoner->save(); }

$prisoner->refresh();
$prisoner->load("cases.institution");
$case = $prisoner->cases->first();
$onDisk = $prisoner->photo && Storage::disk("public")->exists($prisoner->photo);

echo "\n  ", $prisoner->name, "  [/prisoner/", $prisoner->slug, "]\n";
echo "    names       ", $prisoner->first_name, " / ", $prisoner->middle_name, " / ", $prisoner->last_name, "\n";
echo "    aka         ", $prisoner->aka, "\n";
echo "    born/died   ", $prisoner->birthdate, "  ", $prisoner->death_date, "\n";
echo "    era         ", $prisoner->era, "    state ", $prisoner->state, "\n";
echo "    ideologies  ", implode(", ", (array) $prisoner->ideologies), "\n";
echo "    sort_order  ", $prisoner->sort_order, "\n";
echo "    photo       ", $prisoner->photo, "  ", ($onDisk ? "on disk" : "MISSING ON DISK"), "\n";

if ($case) {
    echo "\n    institution ", $case->institution?->name, " — ", $case->institution?->city, ", ", $case->institution?->state, "\n";
    echo "    confined    ", $case->incarceration_date?->toDateString(), "\n";
    echo "    out         ", $case->release_date?->toDateString(), "\n";
    echo "    arrest_date ", ($case->arrest_date ? $case->arrest_date->toDateString() : "(none — a civil commitment is not an arrest)"), "\n";
    echo "    days        ", $case->imprisoned_for_days, "   (expected ", $payload["expected"]["imprisoned_for_days"], ")\n";
    echo "    convicted   ", $case->convicted, "\n";
}

echo "\n  ", wordwrap($payload["escape_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["sourcing_note"], 72, "\n  "), "\n";

$daysOk = $case && (int) $case->imprisoned_for_days === (int) $payload["expected"]["imprisoned_for_days"];

if (! $daysOk && $case) { echo "\n  !! imprisoned_for_days is ", $case->imprisoned_for_days, ", expected ", $payload["expected"]["imprisoned_for_days"], "\n"; }

if ($onDisk && $daysOk && $prisoner->sort_order > 0) { echo "\nB198-OK\n"; }
'

run_tinker "add-bronner" "B198-OK" "$ADD_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 198 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
