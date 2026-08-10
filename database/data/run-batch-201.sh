#!/usr/bin/env bash
#
# BATCH 201 -- Leonard Roy Frank, three California hospitals, 1962-63.
#
#   HE IS THE ARCHIVES OWN ARGUMENT MADE LITERAL. The hospital records
#   entered his beard, his vegetarianism and his Beatnik life as evidence
#   of abnormality, and that was enough to hold him for seven months and
#   put fifty insulin comas and thirty-five electroshocks through him. He
#   spent the rest of his life on it -- co-founding the Network Against
#   Psychiatric Assault and editing The History of Shock Treatment.
#
#   THREE INSTITUTIONS, THREE CASE ROWS, because a case belongs to one
#   institution. Mt. Zion for three days, Napa State for fifty-six, Twin
#   Pines for a hundred and sixty-eight. Adjacent rows share the transfer
#   date, which is right for a transfer and does not double-count:
#   3 + 56 + 168 is 227, exactly the span from 17 October 1962 to 1 June
#   1963.
#
#   ONE FACT WAS CORRECTED, and it is called out rather than buried:
#   "over 50" insulin comas is written 50. Wikipedia, Mad in America,
#   MindFreedom and Frank own testimony all give fifty insulin-coma and
#   thirty-five electroconvulsive procedures. Thirty-five was already
#   right. One word to revert if a source supports a higher count.
#
#   ELEVEN OTHER EDITS ARE GRAMMAR, each listed in the payload. The ones
#   worth knowing: "involuntary committed" needed the adverb; "due to"
#   was modifying a verb; nobody is "a member of a lifestyle"; and the
#   list of what the doctors counted as symptoms reads harder as three
#   parallel nouns than as a chain of gerunds.
#
#   THE CHARACTERISATION IS THE CURATORS AND STANDS: medical experiments,
#   not treatments. Frank argued for forty years that what was done to
#   him was assault rather than medicine, so the word is his position as
#   much as the curators.
#
#   JUNE 1963, NOT 1 JUNE. The Twin Pines release is stored as 1963-06-01
#   and marked month precision so it renders as June 1963. The curator
#   gave both forms; the first of the month is a placeholder, not a
#   recorded day, and the record should not pretend otherwise.
#
#   THE PHOTOGRAPH is small -- 197x241 -- and that is the original upload
#   rather than a thumbnail; the only other sizes Mad in America serves
#   are smaller still. Installed unmodified. Worth replacing if a
#   higher-resolution portrait ever surfaces.
#
#   Idempotent: prisoner:add refuses on a duplicate name; the photo and
#   precision steps are safe to repeat.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-201.sh

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
echo "  Batch 201 — Leonard Roy Frank"
echo "==================================================================="

SRC="database/data/files/prisoners/leonard-roy-frank.jpg"
DEST_DIR="storage/app/public/prisoners"

echo
echo "--- install-photo"
install_ok=1
mkdir -p "$DEST_DIR"
if [ ! -f "$SRC" ]; then
    echo "  missing source file: $SRC"; install_ok=0
else
    dest="$DEST_DIR/leonard-roy-frank.jpg"
    if [ -f "$dest" ] && cmp -s "$SRC" "$dest"; then
        echo "  leonard-roy-frank.jpg — already installed, identical"
    else
        cp "$SRC" "$dest"
        echo "  leonard-roy-frank.jpg — $(stat -c%s "$dest") bytes installed"
    fi
    if [ ! -e "public/storage/prisoners/leonard-roy-frank.jpg" ]; then
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

$payload = json_decode(File::get(base_path("database/data/fixes/batch201.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = $payload["prisoner"];

$prisoner = Prisoner::withoutGlobalScopes()->where("name", $p["name"])->first();

if ($prisoner) {
    echo "  ", $p["name"], " already exists [", $prisoner->slug, "] — not created again.\n";
} else {
    // The artisan command is the supported path: duplicate guard, institution
    // creation, and sort_order placed beside peers rather than left at 0.
    Artisan::call("prisoner:add", ["json" => json_encode($p)]);
    echo Artisan::output();

    $prisoner = Prisoner::withoutGlobalScopes()->where("name", $p["name"])->first();
}

if (! $prisoner) { echo "  !! prisoner was not created — stopping.\n"; return; }

$want = "prisoners/".$prisoner->slug.".jpg";
$installed = "prisoners/".$payload["photo"]["file"];

if ($want !== $installed && Storage::disk("public")->exists($installed)) {
    Storage::disk("public")->put($want, Storage::disk("public")->get($installed));
    echo "  slug is ", $prisoner->slug, " — photo copied to ", $want, "\n";
}

if ($prisoner->photo !== $want) { $prisoner->photo = $want; $prisoner->save(); }

$prisoner->refresh();
$prisoner->load("cases.institution");

// June 1963, not the first of June: the day is a placeholder.
$twin = $prisoner->cases->first(fn ($c) => str_contains((string) $c->institution?->name, "Twin Pines"));

if ($twin && ($twin->date_precision["release_date"] ?? null) !== "month") {
    $twin->date_precision = array_merge($twin->date_precision ?? [], ["release_date" => "month"]);
    $twin->save();
    $twin->refresh();
    echo "  Twin Pines release_date set to month precision — renders as June 1963\n";
}

$onDisk = $prisoner->photo && Storage::disk("public")->exists($prisoner->photo);
$words = str_word_count(strip_tags((string) $prisoner->description));

echo "\n  ", $prisoner->name, "  [/prisoner/", $prisoner->slug, "]\n";
echo "    born / died  ", $prisoner->birthdate, "   ", $prisoner->death_date, "\n";
echo "    era / state  ", $prisoner->era, "   ", $prisoner->state, "\n";
echo "    ideologies   ", implode(", ", (array) $prisoner->ideologies), "\n";
echo "    affiliation  ", implode(", ", (array) $prisoner->affiliation), "\n";
echo "    sort_order   ", $prisoner->sort_order, "\n";
echo "    photo        ", $prisoner->photo, "  ", ($onDisk ? Storage::disk("public")->size($prisoner->photo)." bytes on disk" : "MISSING ON DISK"), "\n";
echo "    description  ", $words, " words   (expected ", $payload["expected"]["description_words"], ")\n";

$prisoner->load("cases.institution");
$total = 0; $days = [];

echo "\n    confinements:\n";

foreach ($prisoner->cases->sortBy("incarceration_date") as $c) {
    $days[] = (int) $c->imprisoned_for_days;
    $total += (int) $c->imprisoned_for_days;
    echo "      ", str_pad((string) $c->institution?->name, 22),
        $c->incarceration_date?->toDateString(), " -> ",
        str_pad($c->formatPartialDate("release_date") ?: "", 12),
        str_pad((string) $c->imprisoned_for_days, 5), " days\n";
}

echo "\n    days per case: ", implode(", ", $days), "   (expected ", implode(", ", $payload["expected"]["days"]), ")\n";
echo "    total:         ", $total, "   (expected ", $payload["expected"]["total_days"], ")\n";

echo "\n  FACT CORRECTED — ", $payload["fact_correction"]["what"], "\n  ",
    wordwrap($payload["fact_correction"]["why"], 70, "\n  "), "\n";

echo "\n  Grammar edits applied to the supplied text (", count($payload["edits"]), "):\n";

foreach ($payload["edits"] as $i => $e) { echo "\n   ", $i + 1, ". ", wordwrap($e, 68, "\n      "), "\n"; }

echo "\n  ", wordwrap($payload["not_changed"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["researched_note"], 72, "\n  "), "\n";

$ok = $onDisk
    && $prisoner->cases->count() === (int) $payload["expected"]["cases"]
    && $days === $payload["expected"]["days"]
    && $total === (int) $payload["expected"]["total_days"]
    && $words === (int) $payload["expected"]["description_words"]
    && $prisoner->sort_order > 0
    && $twin && ($twin->date_precision["release_date"] ?? null) === "month";

if ($ok) { echo "\nB201-OK\n"; }
'

run_tinker "add-frank" "B201-OK" "$ADD_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 201 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
