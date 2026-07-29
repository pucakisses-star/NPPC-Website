#!/usr/bin/env bash
#
# BATCH 18 -- researched birthdates, round 12.
#
#   fix-birthdates-researched-12  four records get source-stated dates
#                                 out of 36 names researched: Ted
#                                 Goertzel (Library of Congress name
#                                 authority, from his own data sheet),
#                                 Donald Mark Johnson (Pennsylvania
#                                 docket for his own case), Jake
#                                 Sherman (January 1982, month
#                                 precision) and Bob Graf of the
#                                 Milwaukee 14 (1943). Three found
#                                 dates were refused: one resting on
#                                 name-plus-city only, one from a
#                                 tracking site, one from
#                                 auto-generated bio pages.
#
# NO PLACEMENT OR RECOMPUTE TAIL. Nothing here creates a record, moves
# one, or touches a case date.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-18.sh

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
echo "  Batch 18 — researched birthdates, round 12"
echo "==================================================================="

run "fix-birthdates-researched-12" bash database/data/fix-birthdates-researched-12.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 18 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
