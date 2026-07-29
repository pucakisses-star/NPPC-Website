#!/usr/bin/env bash
#
# BATCH 16 -- Wesley Somers life dates.
#
#   fix-wesley-somers   The record said a living man was dead: birthdate
#                       2001-05-09 with a death date of 1975-07-31,
#                       twenty-six years before it. The Davidson County
#                       Criminal Court Clerk record for his own case
#                       gives 1995-02-16, and no source reports any
#                       death, so the birthdate is corrected and the
#                       death date cleared. Also corrects the fire date
#                       in the bio to May 30, 2020 and fills in the
#                       March 23, 2022 sentencing.
#
# NO PLACEMENT TAIL. Nothing is created or moved.
#
# The age recomputes on save, so no recompute pass is needed either.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-16.sh

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
echo "  Batch 16 — Wesley Somers life dates"
echo "==================================================================="

run "fix-wesley-somers" bash database/data/fix-wesley-somers.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 16 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
