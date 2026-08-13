#!/usr/bin/env bash
#
# BATCH 212 -- Roberto Rivera: display name back, middle name kept.
#
#   BATCH 210 RENAMED THE RECORD to Roberto E. Rivera. That was not asked
#   for. The middle name was supplied as a fact, not as an instruction to
#   change the heading -- and it is the second time in this run that a
#   record was renamed to a longer form unasked, after Clennon King in
#   batch 202. The heading goes back to Roberto Rivera.
#
#   EPIFANIO STAYS, in the middle_name column. Nothing on the site
#   displays it: the prisoner page heading, the API, the search and the
#   citation partial all read the name column, so the record holds his
#   full name as data while showing the short one. Bronner keeps Theodore
#   the same way, and Clennon King keeps Washington.
#
#   ORDER DOES NOT MATTER. If 210 has run, this renames back and keeps
#   the middle name it set. If it has not, the name is already right and
#   only the middle name is written.
#
#   THE SLUG WAS NEVER IN PLAY. HasSlug generates only on create, so
#   roberto-rivera has survived every rename in this sequence and the
#   page has never moved. His photograph is keyed on that slug too.
#
#   Idempotent: fields written only when they differ.
#
# Run from the repo root, after git pull, after 210 and 211:
#   bash database/data/run-batch-212.sh

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
echo "  Batch 212 — Roberto Rivera: display name back, Epifanio kept"
echo "==================================================================="

NAME_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch212.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = $payload["prisoner"];

$prisoner = Prisoner::withoutGlobalScopes()->where("slug", $p["slug"])->first();

if (! $prisoner) { echo "  no prisoner at slug ", $p["slug"], " — nothing changed.\n"; return; }

$was = $prisoner->name;
$changed = [];

foreach (["name", "first_name", "middle_name", "last_name"] as $f) {
    if ($prisoner->{$f} !== $p[$f]) { $prisoner->{$f} = $p[$f]; $changed[] = $f; }
}

if ($changed) { $prisoner->save(); $prisoner->refresh(); }

echo "  ", $was, "  ->  ", $prisoner->name, "\n";
echo "  set: ", ($changed ? implode(", ", $changed) : "nothing — already correct"), "\n";

echo "\n  heading shows   ", $prisoner->name, "\n";
echo "  name parts      ", $prisoner->first_name, " / ", $prisoner->middle_name, " / ", $prisoner->last_name,
    "   (middle name held as data, never displayed)\n";
echo "  slug            ", $prisoner->slug, "   (/prisoner/", $prisoner->slug, " — never moved)\n";
echo "  aka             ", ($prisoner->aka ?: "(none)"), "\n";

// The rest of the record should be exactly as batches 210 and 211 left it.
echo "\n  untouched by this batch:\n";
echo "    death_date    ", ($prisoner->death_date ?: "(none)"), "\n";
echo "    flags         in_custody ", ($prisoner->in_custody ? "true" : "false"),
    ", released ", ($prisoner->released ? "true" : "false"), "\n";
echo "    description   ", mb_strlen((string) $prisoner->description), " chars, begins: ",
    mb_strimwidth((string) $prisoner->description, 0, 60, "..."), "\n";

echo "\n  ", wordwrap($payload["why_middle_name_stays"], 72, "\n  "), "\n";

$ok = $prisoner->name === $payload["expected"]["name"]
    && $prisoner->middle_name === $payload["expected"]["middle_name"]
    && $prisoner->slug === $payload["expected"]["slug"];

if ($ok) { echo "\nB212-OK\n"; }
'

run_tinker "restore-display-name" "B212-OK" "$NAME_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 212 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
