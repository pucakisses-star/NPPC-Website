#!/usr/bin/env bash
#
# BATCH 2 -- only the work added since run-pending.sh was applied.
#
# run-pending.sh is FROZEN. It is the record of the first batch and it
# re-runs twenty-odd scripts every time it is called, which is wasted
# minutes once that batch has landed. Nothing new gets added to it.
# Everything from here on goes in a batch script like this one, and each
# batch is retired the same way once it has been run.
#
# CONTENTS OF THIS BATCH:
#
#   merge-iww-affiliation           the two Industrial Workers of the
#                                   World spellings become one. Included
#                                   because it was added to run-pending
#                                   at the very end and may not have
#                                   been applied yet; it is idempotent,
#                                   so if it already ran this costs a
#                                   second and reports nothing to do.
#   remove-veterans-rights-ideology retire Veterans{39} Rights, 9 records
#   fix-ralph-ginzburg              release October 10 not 11 (236 days,
#                                   not 237), and the Lewisburg
#                                   reception recorded alongside
#                                   Allenwood
#
# NO PLACEMENT TAIL. Nothing here creates a record or moves one, so
# prisoners:place-zero-sort-by-year has nothing to place.
#
# The Ginzburg fix DOES change a case date, but it writes the case row
# itself, and the day counter is recomputed by the model on save -- so
# no recompute pass is needed either. If a later batch changes dates
# without saving the case (a flag on the prisoner, say), that batch
# needs prisoners:recompute-imprisonment --apply at the end.
#
# One failing step does not abort the run: failures are collected and
# listed at the end.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-2.sh

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
echo "  Batch 2 — taxonomy and one correction"
echo "==================================================================="

run "merge-iww-affiliation" bash database/data/merge-iww-affiliation.sh
run "remove-veterans-rights-ideology" bash database/data/remove-veterans-rights-ideology.sh
run "fix-ralph-ginzburg" bash database/data/fix-ralph-ginzburg.sh
run "fix-free-expression-sort" bash database/data/fix-free-expression-sort.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 2 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
