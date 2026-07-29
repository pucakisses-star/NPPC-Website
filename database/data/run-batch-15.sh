#!/usr/bin/env bash
#
# BATCH 15 -- add Zebulon Vance.
#
#   add-zebulon-vance   Governor of North Carolina, arrested on his
#                       thirty-fifth birthday (May 13, 1865), held at
#                       the Old Capitol Prison in Washington without
#                       charge or trial, paroled July 6, 1865 after 54
#                       days.
#
# PLACEMENT TAIL REQUIRED: this batch creates a record, which lands at
# sort_order 0 and has to be slotted into the chronological order.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-15.sh

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
echo "  Batch 15 — Zebulon Vance"
echo "==================================================================="

run "add-zebulon-vance" bash database/data/add-zebulon-vance.sh
run "place new record in the sort order" php artisan prisoners:place-zero-sort-by-year --apply

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 15 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
