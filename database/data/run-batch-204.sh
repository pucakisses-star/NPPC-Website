#!/usr/bin/env bash
#
# BATCH 204 -- the record goes back to "Clennon King".
#
#   BATCH 202 RENAMED IT to Clennon Washington King Jr., because that is
#   how the entry was titled when it was handed over. The curator wants
#   the displayed name back to Clennon King. This sets it back.
#
#   202 IS LEFT ALONE rather than edited, which is how 186 superseded
#   185. That batch is merged but has not run on the server, so on a
#   first run 202 sets the long name and this sets it straight back; on a
#   server where 202 already ran, this corrects it. Either order
#   converges on the same record.
#
#   THE NAME PARTS STAY Clennon / Washington / King. Nothing on the site
#   displays them -- the prisoner page heading, the API and the search
#   all read the name column -- so the record keeps the full form of his
#   name as data while showing the short one. Bronner gets the same
#   treatment with Theodore.
#
#   THE SLUG WAS NEVER IN PLAY. HasSlug generates only on create, so
#   clennon-king has survived both renames untouched and
#   /prisoner/clennon-king has never moved. The photograph attached in
#   203 is keyed on that slug, so it is unaffected too.
#
#   Idempotent: the name is written only when it differs.
#
# Run from the repo root, after git pull, after batches 202 and 203:
#   bash database/data/run-batch-204.sh

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
echo "  Batch 204 — back to Clennon King"
echo "==================================================================="

RENAME_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch204.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = $payload["prisoner"];

$prisoner = Prisoner::withoutGlobalScopes()->where("slug", $p["slug"])->first();

if (! $prisoner) { echo "  no prisoner at slug ", $p["slug"], " — nothing changed.\n"; return; }

$was = $prisoner->name;

if ($prisoner->name !== $p["name"]) {
    $prisoner->name = $p["name"];
    $prisoner->save();
    $prisoner->refresh();
    echo "  name: ", $was, "  ->  ", $prisoner->name, "\n";
} else {
    echo "  already named ", $prisoner->name, " — nothing to do.\n";
}

$photoOk = $prisoner->photo && Storage::disk("public")->exists($prisoner->photo);

echo "\n  heading shows  ", $prisoner->name, "\n";
echo "  slug           ", $prisoner->slug, "   (/prisoner/", $prisoner->slug, " — never moved)\n";
echo "  name parts     ", $prisoner->first_name, " / ", $prisoner->middle_name, " / ", $prisoner->last_name,
    "   (kept as data, not displayed)\n";
echo "  aka            ", ($prisoner->aka ?: "(none)"), "\n";
echo "  photo          ", ($prisoner->photo ?: "(none)"), "  ",
    ($photoOk ? "still attached" : "NOT ON DISK — check batch 203"), "\n";

$case = $prisoner->cases()->first();

if ($case) {
    echo "  confinement    ", optional($case->incarceration_date)->toDateString(), " -> ",
        optional($case->release_date)->toDateString(), "   ", $case->imprisoned_for_days, " days\n";
}

// A rename must not have produced a second record or moved the page.
$clennons = Prisoner::withoutGlobalScopes()->where("name", "like", "%Clennon%")->get(["name", "slug"]);

echo "\n  records matching Clennon: ", $clennons->count(), "\n";

foreach ($clennons as $c) { echo "    ", $c->name, "  [", $c->slug, "]\n"; }

echo "\n  ", wordwrap($payload["name_parts_note"], 72, "\n  "), "\n";

$ok = $prisoner->name === $payload["expected"]["name"]
    && $prisoner->slug === $payload["expected"]["slug"]
    && $clennons->count() === 1;

if ($ok) { echo "\nB204-OK\n"; }
'

run_tinker "rename-back" "B204-OK" "$RENAME_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 204 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
