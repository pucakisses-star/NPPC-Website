#!/usr/bin/env bash
#
# BATCH 100 -- the DOB audit: exact birth dates for 65 deceased
# records that carried year-only birthdates.
#
#   A ten-agent research sweep covered ALL 253 deceased records with
#   partial-precision birthdates. Every date entered here required a
#   citable source (encyclopedias, obituaries, congressional
#   bioguides, archival finding aids, grave records) whose subject
#   matched the record on at least TWO facts beyond the name — the
#   death date foremost, plus the case story or location.
#   People-search/data-broker sites were forbidden. 174 records came
#   back honestly NOT FOUND (for most, year-only is the true limit
#   of the historical record) and 14 came back with CONFLICTS —
#   usually a death-date discrepancy — and are held out for curator
#   review.
#
#   Full per-person sources, the conflict list, and the data-quality
#   flags the sweep surfaced live in database/data/DOB-AUDIT-NOTES.md.
#
#   THE SCRIPT NEVER OVERWRITES AN EXISTING DAY-PRECISION BIRTHDATE —
#   only records still at year/month precision are upgraded.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-100.sh

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
echo "  Batch 100 — DOB audit: 65 confirmed exact birth dates"
echo "==================================================================="

fix_dobs() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/dob-audit-confirmed.json")), true);

if (! $payload) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

$set = 0;
$kept = 0;

foreach ($payload["people"] as $entry) {
    $p = Prisoner::withUnderReview()->where("slug", $entry["slug"])->first();

    if (! $p) {
        echo str_pad($entry["slug"], 32), "NOT FOUND\n";
        continue;
    }

    [$y, $m, $d] = $entry["birthdate"];
    $target = sprintf("%04d-%02d-%02d", $y, $m, $d);

    if ($p->datePrecisionFor("birthdate") === "day") {
        $have = $p->birthdate ? $p->birthdate->format("Y-m-d") : "?";
        echo str_pad($entry["slug"], 32), "already day precision (", $have, ") — left alone\n";
        $kept++;
        continue;
    }

    $was = $p->birthdate ? $p->birthdate->format("Y") : "none";
    $p->setPartialDate("birthdate", $y, $m, $d);
    $p->save();
    echo str_pad($entry["slug"], 32), "birthdate=", $target, " (was year ", $was, ")\n";
    $set++;
}

echo "\nset: ", $set, "   left alone (already exact): ", $kept, "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-dob-audit" fix_dobs

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 100 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
