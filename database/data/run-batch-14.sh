#!/usr/bin/env bash
#
# BATCH 14 -- researched birthdates, round 11.
#
#   fix-birthdates-researched-11  six records get source-stated dates
#                                 out of 36 names researched: Ed
#                                 Sanders, William Durkin Jr. of the
#                                 Chicago 15, Siobhan Browne and Mark
#                                 Warren Sands (stated birthdays in
#                                 profiles), Caleb A. Brown (court
#                                 record), and a death date for Pvt.
#                                 Leroy Pinkett (birth left null on
#                                 conflicting stated dates). One
#                                 data-broker date was refused.
#                                 Twenty-nine names stay null.
#
# NO PLACEMENT OR RECOMPUTE TAIL. Nothing here creates a record, moves
# one, or touches a case date.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-14.sh

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
echo "  Batch 14 — researched birthdates, round 11"
echo "==================================================================="

run "fix-birthdates-researched-11" bash database/data/fix-birthdates-researched-11.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 14 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
