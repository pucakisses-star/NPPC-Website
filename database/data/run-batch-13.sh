#!/usr/bin/env bash
#
# BATCH 13 -- researched birthdates, round 10.
#
#   fix-birthdates-researched-10  nine records get source-stated
#                                 dates out of 36 names researched:
#                                 John Hinshaw and Duncan Small
#                                 (court-document DOBs), Alex Jason
#                                 Hall, Brenda Travis, Carolyn Long
#                                 Banks, Armando Gomez Espana
#                                 (Colombian Supreme Court ruling),
#                                 Lisa Leggio, Wilmer Young, and a
#                                 death date for Tearra Guthrie.
#                                 Twenty-five names stay null; two
#                                 obituary matches were excluded for
#                                 lacking a case link.
#
# NO PLACEMENT OR RECOMPUTE TAIL. Nothing here creates a record, moves
# one, or touches a case date.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-13.sh

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
echo "  Batch 13 — researched birthdates, round 10"
echo "==================================================================="

run "fix-birthdates-researched-10" bash database/data/fix-birthdates-researched-10.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 13 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
