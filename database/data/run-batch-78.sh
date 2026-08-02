#!/usr/bin/env bash
#
# BATCH 78 -- Black Panther Party affiliation for the five Flight 841
# hijackers: Melvin McNair, Jean McNair, Joyce Tillerson, George
# Brown and George Wright.
#
#   "Black Panther Party" is APPENDED to each affiliation list —
#   existing entries (Black Liberation Army on Tillerson, Brown and
#   Wright) are kept. Jean McNair is matched by either her old slug
#   (jean-mcnair) or the batch 74 rename (jean-carol-allen-mcnair).
#   The george-brown slug was verified against the record text — the
#   OTHER George Brown in the database (george-william-brown, the
#   Civil War-era Baltimore mayor) is untouched.
#
# Idempotent: appends only when absent.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-78.sh

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
echo "  Batch 78 — Black Panther Party affiliation for the Flight 841 five"
echo "==================================================================="

fix_affiliations() {
    php artisan tinker --execute='
use App\Models\Prisoner;

$groups = [
    ["melvin-mcnair"],
    ["jean-carol-allen-mcnair", "jean-mcnair"],
    ["joyce-tillerson"],
    ["george-brown"],
    ["george-wright"],
];

foreach ($groups as $slugs) {
    $p = Prisoner::withUnderReview()->whereIn("slug", $slugs)->first();

    if (! $p) {
        echo str_pad($slugs[0], 26), "NOT FOUND\n";
        continue;
    }

    $aff = $p->affiliation ?? [];

    if (in_array("Black Panther Party", $aff, true)) {
        echo str_pad($p->slug, 26), "already affiliated\n";
        continue;
    }

    $aff[] = "Black Panther Party";
    $p->affiliation = array_values($aff);
    $p->save();
    echo str_pad($p->slug, 26), "affiliation now: ", implode(", ", $p->affiliation), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-flight841-affiliations" fix_affiliations

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 78 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
