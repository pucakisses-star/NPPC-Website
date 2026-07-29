#!/usr/bin/env bash
#
# BATCH 8 -- researched birthdates, round 5.
#
#   fix-birthdates-researched-5   nine records get source-stated dates
#                                 out of 36 names researched: Ed
#                                 Poindexter of the Omaha Two (the
#                                 alex-poindexter record), Agnes
#                                 Bauerlein, David Michael Ansberry
#                                 (complaint DOB), Briana Boston (jail
#                                 booking DOB), Randy Rowland of the
#                                 Presidio 27 (1947), Major James
#                                 McFarlane of the Whiskey Rebellion,
#                                 and death dates for Daniel Jongyon
#                                 Park, James Edward Garrett and
#                                 Chester Jackson. Sources in the
#                                 script header. Twenty-seven names
#                                 stay null.
#
# NO PLACEMENT OR RECOMPUTE TAIL. Nothing here creates a record, moves
# one, or touches a case date.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-8.sh

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
echo "  Batch 8 — researched birthdates, round 5"
echo "==================================================================="

run "fix-birthdates-researched-5" bash database/data/fix-birthdates-researched-5.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 8 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
