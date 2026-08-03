#!/usr/bin/env bash
#
# BATCH 111 -- record removals, per the curator:
#
#   THE DAWSON FIVE — all seven records covering the group
#   (roosevelt-watson, henderson-watson, jd-davenport,
#   johnnie-b-jackson and its duplicate johnny-jackson,
#   james-jackson-jr, and george-poor, whose record names him as one
#   of the group) are removed.
#
#   ERIC ABRAHAM — the South African journalist was banned and
#   house-arrested, then escaped to asylum; removed.
#
#   Each removal deletes the record's case rows and any
#   auto-generated calendar entries, then the record itself.
#   Podcast episodes, if any referenced these records, keep their
#   rows (the foreign key sets null). Removals are idempotent: an
#   already-absent slug just reports as such.
#
# Run from the repo root, after git pull (after batch 110):
#   bash database/data/run-batch-111.sh

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
echo "  Batch 111 — remove the Dawson Five + Eric Abraham"
echo "==================================================================="

remove_records() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\CalendarEntry;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch111.json")), true);

if (! $payload || empty($payload["removals"])) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

foreach ($payload["removals"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->with("cases")->first();

    echo "\nREMOVE ", $row["slug"], "\n";

    if (! $p) { echo "  not found (already removed?)\n"; continue; }

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

run "remove-dawson-five-and-abraham" remove_records

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 111 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
