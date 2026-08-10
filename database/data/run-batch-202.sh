#!/usr/bin/env bash
#
# BATCH 202 -- Frank's affiliation removed; Clennon King filled in;
# Elizabeth Packard added.
#
#   KING WAS ALREADY HERE, and this batch nearly created a second him.
#   He is in the database as "Clennon King" at slug clennon-king with
#   sort_order 3998. prisoner:add guards duplicates by exact name, so
#   "Clennon Washington King Jr." would have walked straight past it. The
#   check that caught it was running the name against the live database
#   before writing anything, which is the only thing that catches this
#   class of near-miss.
#
#   SO KING IS AN UPDATE, NOT A CREATE. The existing record has the right
#   story and no dates at all: imprisoned_for_days sits at 0, the case
#   carries neither an incarceration nor a release date, and the twelve
#   days are stranded in the free-text sentence field where nothing can
#   count them. This batch adds the name parts, the birth and death
#   dates, the curator's new facts, and the three dates that make the
#   twelve days computable.
#
#   ONLY THE DATES GO ON THE CASE. charges, convicted and sentence
#   already say the right thing and are left alone -- charges is stored
#   as an array on this record, and rewriting it as a string to say what
#   it already says would be a needless risk.
#
#   THE SLUG STAYS clennon-king. HasSlug only generates on create, so
#   renaming does not move the page and nothing linking to
#   /prisoner/clennon-king breaks.
#
#   HIS JUNE 6 ADMISSION DATE IS ARITHMETIC, not a source. Sourced: the
#   June 5 removal and jailing, and the UPI release on June 18 after
#   twelve days. Eighteen minus twelve gives the sixth, and storing it
#   makes the twelve days computable instead of asserted.
#
#   KING STILL HAS NO PHOTOGRAPH, deliberately. Commons has nothing, the
#   Wikipedia article carries no image, the Mississippi Encyclopedia
#   entry has none, and the 1958 press photographs are copyrighted. One
#   lead worth chasing: MDAH holds a photograph of him in the Sovereignty
#   Commission records, catalogued 1-28-0-94-1-1-1ph.
#
#   PACKARD IS GENUINELY NEW -- checked, nothing matching in 8,594
#   records. Committed on June 18, 1860 by her husband under an Illinois
#   statute that let a husband commit his wife on his word alone, with
#   none of the hearing the law required in every other case. Three years
#   to the day, 1,095 of them. She came out and spent her life on it,
#   founding the Anti-Insane Asylum Society and winning the personal
#   liberty statutes still called Packard laws. Her portrait is public
#   domain via Wikimedia Commons, 200x392, the full size Commons holds.
#
#   FRANK: the Network Against Psychiatric Assault affiliation comes off,
#   as asked. It was a researched addition in 201, and NAPA post-dates
#   the confinement by eleven years anyway.
#
#   Idempotent throughout: fields written only when they differ.
#
# Run from the repo root, after git pull, after batch 201:
#   bash database/data/run-batch-202.sh

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
echo "  Batch 202 — Frank, Clennon King, Elizabeth Packard"
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
        echo "  elizabeth-packard.jpg — already installed, identical"
    else
        cp "$SRC" "$dest"
        echo "  elizabeth-packard.jpg — $(stat -c%s "$dest") bytes installed"
    fi
    if [ ! -e "public/storage/prisoners/elizabeth-packard.jpg" ]; then
        echo "  !! not reachable through the public symlink — run php artisan storage:link"
        install_ok=0
    fi
fi
[ "$install_ok" -eq 1 ] || FAILED+=("install-photo")

MAIN_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch202.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$problems = [];

// --- Frank: affiliation off ------------------------------------------------
$f = $payload["frank"];
$frank = Prisoner::withoutGlobalScopes()->where("slug", $f["slug"])->first();

if (! $frank) {
    echo "  no prisoner at slug ", $f["slug"], " — run batch 201 first.\n";
    $problems[] = "frank missing";
} else {
    $was = (array) $frank->affiliation;

    if ($was) {
        $frank->affiliation = [];
        $frank->save();
        $frank->refresh();
        echo "  ", $frank->name, ": affiliation cleared (was ", implode(", ", $was), ")\n";
    } else {
        echo "  ", $frank->name, ": affiliation already empty.\n";
    }

    if ((array) $frank->affiliation) { $problems[] = "frank affiliation not cleared"; }
}

// --- King: fill in the record that was already here ------------------------
echo "\n  ---------------------------------------------------------------\n";

$k = $payload["king_update"];
$king = Prisoner::withoutGlobalScopes()->where("slug", $k["slug"])->first();

if (! $king) {
    echo "  !! no prisoner at slug ", $k["slug"], " — expected the existing record. Nothing done for King.\n";
    $problems[] = "king missing";
} else {
    $wasName = $king->name;
    $changed = [];

    foreach (["name", "first_name", "middle_name", "last_name", "description"] as $field) {
        if ($king->{$field} !== $k[$field]) { $king->{$field} = $k[$field]; $changed[] = $field; }
    }

    foreach (["birthdate", "death_date"] as $field) {
        if ((string) $king->{$field} !== $k[$field]) { $king->{$field} = $k[$field]; $changed[] = $field; }
    }

    if ($changed) { $king->save(); $king->refresh(); }

    echo "  ", $wasName, "  ->  ", $king->name, "   [/prisoner/", $king->slug, "]\n";
    echo "    set:         ", ($changed ? implode(", ", $changed) : "nothing — already correct"), "\n";
    echo "    born / died  ", $king->birthdate, "   ", $king->death_date, "\n";
    echo "    sort_order   ", $king->sort_order, "  (untouched)\n";

    // Dates only. charges/convicted/sentence already say the right thing, and
    // charges is an array on this record.
    $case = $king->cases()->first();

    if (! $case) {
        echo "    !! no case row on the existing record\n";
        $problems[] = "king case missing";
    } else {
        $caseChanged = [];

        foreach ($k["case_dates"] as $field => $value) {
            if (optional($case->{$field})->toDateString() !== $value) { $case->{$field} = $value; $caseChanged[] = $field; }
        }

        if ($caseChanged) { $case->save(); $case->refresh(); }

        echo "    case dates:  ", ($caseChanged ? implode(", ", $caseChanged) : "nothing — already correct"), "\n";
        echo "      arrested   ", optional($case->arrest_date)->toDateString(), "\n";
        echo "      confined   ", optional($case->incarceration_date)->toDateString(), " -> ",
            optional($case->release_date)->toDateString(), "   ", $case->imprisoned_for_days, " days\n";
        echo "      charges/convicted/sentence left exactly as they were\n";
    }
}

// --- Packard: genuinely new ------------------------------------------------
foreach ($payload["prisoners"] as $p) {
    echo "\n  ---------------------------------------------------------------\n";

    $prisoner = Prisoner::withoutGlobalScopes()->where("name", $p["name"])->first();

    if ($prisoner) {
        echo "  ", $p["name"], " already exists [", $prisoner->slug, "] — not created again.\n";
    } else {
        Artisan::call("prisoner:add", ["json" => json_encode($p)]);
        echo Artisan::output();

        $prisoner = Prisoner::withoutGlobalScopes()->where("name", $p["name"])->first();
    }

    if (! $prisoner) { $problems[] = $p["name"]." was not created"; echo "  !! not created\n"; continue; }

    $prisoner->load("cases.institution");

    echo "  ", $prisoner->name, "  [/prisoner/", $prisoner->slug, "]\n";
    echo "    born / died  ", $prisoner->birthdate, "   ", $prisoner->death_date, "\n";
    echo "    era / state  ", $prisoner->era, "   ", $prisoner->state, "\n";
    echo "    sort_order   ", $prisoner->sort_order, "\n";

    foreach ($prisoner->cases as $c) {
        echo "    case         ", $c->institution?->name, " — ", $c->institution?->city, ", ", $c->institution?->state, "\n";
        echo "      confined   ", optional($c->incarceration_date)->toDateString(), " -> ",
            optional($c->release_date)->toDateString(), "   ", $c->imprisoned_for_days, " days\n";
    }

    if ($prisoner->sort_order <= 0) { $problems[] = $prisoner->slug." sort_order is 0"; }
}

foreach ($payload["photos"] as $ph) {
    $prisoner = Prisoner::withoutGlobalScopes()->where("slug", $ph["slug"])->first();

    if (! $prisoner) { $problems[] = $ph["slug"]." not found for photo"; continue; }

    $want = "prisoners/".$prisoner->slug.".jpg";

    if (! Storage::disk("public")->exists($want)) {
        $problems[] = $want." not on disk";
        echo "\n  !! photo missing on disk: ", $want, "\n";

        continue;
    }

    if ($prisoner->photo !== $want) { $prisoner->photo = $want; $prisoner->save(); $prisoner->refresh(); }

    echo "\n  photo: ", $prisoner->name, " -> ", $prisoner->photo, "  ",
        Storage::disk("public")->size($prisoner->photo), " bytes\n";
}

// --- assertions ------------------------------------------------------------
$kingDays = $king?->cases()->first()?->imprisoned_for_days;
$packard = Prisoner::withoutGlobalScopes()->where("slug", "elizabeth-packard")->first();
$packardDays = $packard?->cases()->first()?->imprisoned_for_days;

echo "\n  King: ", $kingDays, " days   (expected ", $payload["expected"]["king_days"], ")   photo: ",
    ($king && $king->photo ? $king->photo : "(none — deliberately)"), "\n";
echo "  Packard: ", $packardDays, " days   (expected ", $payload["expected"]["packard_days"], ")\n";

// A second Clennon must not exist.
$clennons = Prisoner::withoutGlobalScopes()->where("name", "like", "%Clennon%")->get(["name", "slug"]);

echo "\n  records matching Clennon: ", $clennons->count(), "\n";

foreach ($clennons as $c) { echo "    ", $c->name, "  [", $c->slug, "]\n"; }

if ($clennons->count() !== 1) { $problems[] = "expected exactly one Clennon record"; }
if ((int) $kingDays !== (int) $payload["expected"]["king_days"]) { $problems[] = "king days"; }
if ((int) $packardDays !== (int) $payload["expected"]["packard_days"]) { $problems[] = "packard days"; }

echo "\n  ", wordwrap($payload["king_update"]["why"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["king_no_photo"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["king_date_note"], 72, "\n  "), "\n";

if (! $problems) { echo "\nB202-OK\n"; }
else { echo "\n  problems: ", implode("; ", $problems), "\n"; }
'

run_tinker "frank-king-packard" "B202-OK" "$MAIN_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 202 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
