#!/usr/bin/env bash
#
# BATCH 7 -- researched birthdates, round 4.
#
#   fix-birthdates-researched-4   eight records get source-stated
#                                 dates out of 36 names researched:
#                                 Cameron Monte Smith (court-filing
#                                 DOB), Michelle Lunsky (Wisconsin
#                                 court record), Joan Bird (1970 NYT
#                                 profile), Chase Iron Eyes
#                                 (Wikipedia), Collis English of the
#                                 Trenton Six (birth year + death in
#                                 prison), Regina Brave (1941), Josh
#                                 Ellerman (1979), and a death date
#                                 for Mohammed Rafiq Butt. Sources in
#                                 the script header. Twenty-eight
#                                 names stay null: only ages on
#                                 record.
#
# NO PLACEMENT OR RECOMPUTE TAIL. Nothing here creates a record, moves
# one, or touches a case date.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-7.sh

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
echo "  Batch 7 — researched birthdates, round 4"
echo "==================================================================="

run "fix-birthdates-researched-4" bash database/data/fix-birthdates-researched-4.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 7 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
