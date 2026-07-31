#!/usr/bin/env bash
#
# BATCH 39 -- Joseph R. Dunlop's vital dates.
#
#   fix-joseph-r-dunlop   Born about 1848 or 1849, died 1926. The birth
#                         year is stored at CIRCA precision, which the
#                         model defines as a year that may be off by one
#                         -- exactly the shape of the source -- and
#                         renders as "c. 1848". The death year is stored
#                         at year precision.
#
# NO CUSTODY CHANGES. His Joliet term is already recorded and internally
# consistent: committed May 4, 1897, released February 10, 1899, 647 days
# against a two-year sentence.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-39.sh

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
echo "  Batch 39 — Joseph R. Dunlop"
echo "==================================================================="

run "fix-joseph-r-dunlop" bash database/data/fix-joseph-r-dunlop.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 39 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
