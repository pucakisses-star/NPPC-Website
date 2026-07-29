#!/usr/bin/env bash
#
# BATCH 19 -- derived birth years, round 1.
#
#   fix-birthyears-derived-1   34 of 36 researched names get a birth
#                              year, each from an age paired with the
#                              date it was reported as-of. Eight are
#                              pinned by intersecting two dated ages and
#                              stored as plain years; one had its month
#                              and year published outright; twenty-five
#                              straddle two calendar years and are
#                              stored circa, displaying "c. 1996".
#
#                              Requires the circa precision, which ships
#                              alongside this. On an older checkout the
#                              circa rows would fall back to plain year
#                              precision -- i.e. they would silently
#                              claim more than the sources support -- so
#                              pull before running.
#
# NO PLACEMENT OR RECOMPUTE TAIL. Nothing is created or moved, and no
# case dates change. Ages recompute from the birthdate on save.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-19.sh

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
echo "  Batch 19 — derived birth years, round 1"
echo "==================================================================="

run "fix-birthyears-derived-1" bash database/data/fix-birthyears-derived-1.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 19 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
