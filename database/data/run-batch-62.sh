#!/usr/bin/env bash
#
# BATCH 62 -- slot every sort_order-0 record into its place.
#
#   php artisan prisoners:sort-new
#
#     Records created by recent batches enter with the schema default
#     sort_order of 0, which drops them to the wrong end of the
#     curated archive order. The new command places each one
#     immediately after the already-sorted record whose date is
#     NEAREST to its own earliest case date — which lands cohort
#     members beside their co-defendants (the Aurora pair beside
#     Diego Vargas, the Everett two beside the Everett defendants,
#     the 1961 Jackson riders among the existing Freedom Riders) —
#     rather than at a naive chronological offset, because the
#     curated order is only loosely chronological and a strict
#     insertion would scatter them. Fallbacks: era midpoint for
#     records with no case dates; the very end of the list, loudly
#     reported, for records with neither dates nor era.
#
#     Idempotent — a second run finds no zero rows. Re-run this
#     command after ANY batch that creates records:
#
#         php artisan prisoners:sort-new
#
#     (or with --dry-run first to preview the placements).
#
# NOTE: run this AFTER batches 55-61 so their new records exist and
# get sorted in the same pass.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-62.sh

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
echo "  Batch 62 — sort the sort_order-0 records into place"
echo "==================================================================="

run "prisoners:sort-new" php artisan prisoners:sort-new

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 62 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
