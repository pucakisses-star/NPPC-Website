#!/usr/bin/env bash
#
# BATCH 5 -- researched birthdates, round 2.
#
#   fix-birthdates-researched-2   four dates found stated in sources
#                                 for the second research chunk of 24
#                                 names: Benjamin Sasway (1960-12-09,
#                                 Solicitor General brief in his own
#                                 case), Jack Gaveel (1889) and Sam
#                                 Povff (1878) from the Zimmer deportee
#                                 pages, and a 1990 death year for
#                                 William Wright Jr. of the Wilmington
#                                 Ten. Twenty names stay null: their
#                                 sources state only ages.
#
# NO PLACEMENT OR RECOMPUTE TAIL. Nothing here creates a record, moves
# one, or touches a case date.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-5.sh

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
echo "  Batch 5 — researched birthdates, round 2"
echo "==================================================================="

run "fix-birthdates-researched-2" bash database/data/fix-birthdates-researched-2.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 5 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
