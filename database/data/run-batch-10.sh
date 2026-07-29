#!/usr/bin/env bash
#
# BATCH 10 -- researched birthdates, round 7.
#
#   fix-birthdates-researched-7   five records get source-stated
#                                 dates out of 36 names researched:
#                                 Joanna Smith (defense sentencing
#                                 memo), Bevelyn Beatty Williams (her
#                                 own stated birthday), Mohammad
#                                 El-Mezain of the Holy Land Five
#                                 (1953), Monzer al-Kassar (1945),
#                                 and a death date for Fr. Ned
#                                 Murphy. Thirty-one names stay null
#                                 -- recent federal filings publish
#                                 only ages.
#
# NO PLACEMENT OR RECOMPUTE TAIL. Nothing here creates a record, moves
# one, or touches a case date.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-10.sh

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
echo "  Batch 10 — researched birthdates, round 7"
echo "==================================================================="

run "fix-birthdates-researched-7" bash database/data/fix-birthdates-researched-7.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 10 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
