#!/usr/bin/env bash
#
# BATCH 4 -- researched birthdates, round 1.
#
#   fix-birthdates-researched-1   seven dates found stated in sources
#                                 (court filings, Wikipedia, movement
#                                 profiles) for records the bio-age
#                                 revert left dateless: Louis Lingg,
#                                 Wesley Robert Wells, Victor Puertas,
#                                 Shamim Mafi, Jamison Wagner, Merle
#                                 Africa, Debbie Africa. Sources are in
#                                 that script header. Fifteen other
#                                 names were researched and stay null
#                                 because no source states a date.
#
# NO PLACEMENT OR RECOMPUTE TAIL. Nothing here creates a record, moves
# one, or touches a case date; the age shown on a profile is computed
# from the birthdate on save and on read.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-4.sh

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
echo "  Batch 4 — researched birthdates, round 1"
echo "==================================================================="

run "fix-birthdates-researched-1" bash database/data/fix-birthdates-researched-1.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 4 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
