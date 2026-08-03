#!/usr/bin/env bash
#
# BATCH 123 -- record removals, per the curator:
#
#   EDWARD R. LOWRY and FRANK FISHER JR. — the two Black US Army
#   privates stationed in New Caledonia who were convicted by a 1943
#   general court-martial and sentenced to life imprisonment (the
#   case the International Labor Defense and Vito Marcantonio
#   denounced as a frame-up, and New Masses headlined "Another
#   Scottsboro?"). Both records are removed.
#
#   Each removal deletes the record's case rows and any
#   auto-generated calendar entries, then the record itself.
#   Podcast episodes, if any referenced these records, keep their
#   rows (the foreign key sets null). Removals are idempotent: an
#   already-absent record just reports as such.
#
#   Records are located by slug first, then by exact (case-
#   insensitive) name. A name that matches more than one record
#   aborts that removal rather than guessing, and every record is
#   printed in full before it is deleted.
#
# Run from the repo root, after git pull (after batch 122):
#   bash database/data/run-batch-123.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"
    shift
    echo
    echo "--- ${label}"
    if "$@"; then
        return 0
    fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}")
    return 0
}

echo "==================================================================="
echo "  Batch 123 — remove Edward R. Lowry and Frank Fisher Jr."
echo "==================================================================="

remove_records() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\CalendarEntry;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch123.json")), true);

if (! $payload || empty($payload["removals"])) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

foreach ($payload["removals"] as $row) {
    echo "\nREMOVE ", $row["slug"], "\n";

    $matches = Prisoner::withUnderReview()
        ->where(fn ($q) => $q->where("slug", $row["slug"])
            ->orWhereRaw("LOWER(name) = ?", [mb_strtolower($row["name"])]))
        ->with("cases")
        ->get();

    if ($matches->isEmpty()) { echo "  not found (already removed?)\n"; continue; }

    if ($matches->count() > 1) {
        echo "  ABORT: ", $matches->count(), " records match. Refusing to guess:\n";
        foreach ($matches as $m) { echo "    ", $m->slug, "  ", $m->name, "\n"; }
        continue;
    }

    $p = $matches->first();

    echo "  record:      ", $p->name, "  [", $p->slug, "]\n";
    echo "  affiliation: ", (is_array($p->affiliation) ? implode(", ", $p->affiliation) : "-"), "\n";
    echo "  ideologies:  ", (is_array($p->ideologies) ? implode(", ", $p->ideologies) : "-"), "\n";
    echo "  photo:       ", ($p->photo ?: "(none)"), "\n";

    $cases = $p->cases->count();
    foreach ($p->cases as $c) { $c->delete(); }

    $cal = CalendarEntry::where("prisoner_id", $p->id)->delete();

    $p->delete();

    echo "  deleted (", $cases, " case rows, ", $cal, " calendar entries) — ", $row["reason"], "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "remove-lowry-and-fisher" remove_records

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 123 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
