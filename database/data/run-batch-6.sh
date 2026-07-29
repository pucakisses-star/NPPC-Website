#!/usr/bin/env bash
#
# BATCH 6 -- researched birthdates, round 3.
#
#   fix-birthdates-researched-3   eleven records get source-stated
#                                 dates out of 36 names researched:
#                                 full birth and death dates for John
#                                 Artis, Frank Dukes, Father Michael
#                                 Doyle and Warren Wells; birth dates
#                                 for Calla Walsh, David McKay, Joan
#                                 Bell and Farouk Abdel-Muhti; death
#                                 dates for Tommy Lee Hines, Ann
#                                 Shepard (anne-sheppard-turner) and
#                                 Bruce Washington. Sources are in the
#                                 script header. Twenty-five names
#                                 stay null: only ages on record.
#
# NO PLACEMENT OR RECOMPUTE TAIL. Nothing here creates a record, moves
# one, or touches a case date.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-6.sh

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
echo "  Batch 6 — researched birthdates, round 3"
echo "==================================================================="

run "fix-birthdates-researched-3" bash database/data/fix-birthdates-researched-3.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 6 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
