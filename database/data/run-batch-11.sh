#!/usr/bin/env bash
#
# BATCH 11 -- researched birthdates, round 8.
#
#   fix-birthdates-researched-8   eight records get source-stated
#                                 dates out of 36 names researched:
#                                 Gene Keyes, Cortez Rice, Christopher
#                                 Lay, Wil Casey Floyd, Chioke Fugate
#                                 (all court/DOC/police-stated DOBs or
#                                 Wikipedia), Jayma Abdoo of the
#                                 Camden 28, William Mackie (SCOTUS
#                                 dissent), and a death date for
#                                 Sarah Tosi. Two found dates were
#                                 EXCLUDED: a DeRisi obituary with no
#                                 case link, and conflicting OFAC
#                                 DOBs for Nancy Conde Rubio.
#                                 Twenty-six names stay null.
#
# NO PLACEMENT OR RECOMPUTE TAIL. Nothing here creates a record, moves
# one, or touches a case date.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-11.sh

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
echo "  Batch 11 — researched birthdates, round 8"
echo "==================================================================="

run "fix-birthdates-researched-8" bash database/data/fix-birthdates-researched-8.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 11 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
