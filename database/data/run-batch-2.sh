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
#
# NO PLACEMENT OR RECOMPUTE TAIL. Neither script touches dates, custody
# or sort_order, so there is nothing for
# prisoners:place-zero-sort-by-year or
# prisoners:recompute-imprisonment to do afterwards. Those only belong
# in a batch that adds records or changes case dates.
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
echo "  Batch 2 — taxonomy only"
echo "==================================================================="

run "merge-iww-affiliation" bash database/data/merge-iww-affiliation.sh
run "remove-veterans-rights-ideology" bash database/data/remove-veterans-rights-ideology.sh

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
