#!/usr/bin/env bash
#
# BATCH 12 -- researched birthdates, round 9.
#
#   fix-birthdates-researched-9   five records get source-stated
#                                 dates out of 36 names researched:
#                                 Lafi Khalil (DOJ OIG report), Jose
#                                 Maria Corredor Ibague (OFAC), Miriam
#                                 Feingold (CRDL + Freedom Rides
#                                 Museum), Eugene Huelsman (docket
#                                 year of birth), and a death date for
#                                 Fr. Nicholas Riddell of the Chicago
#                                 15. Thirty-one names stay null.
#
# NO PLACEMENT OR RECOMPUTE TAIL. Nothing here creates a record, moves
# one, or touches a case date.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-12.sh

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
echo "  Batch 12 — researched birthdates, round 9"
echo "==================================================================="

run "fix-birthdates-researched-9" bash database/data/fix-birthdates-researched-9.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 12 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
