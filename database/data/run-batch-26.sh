#!/usr/bin/env bash
#
# BATCH 26 -- resync the derived imprisoned_or_exiled column.
#
#   resync-imprisoned-or-exiled   82 people the database says are in
#                                 custody were being excluded from the
#                                 public currently-active lists, because
#                                 the derived column had never been
#                                 recomputed on their rows. Nothing was
#                                 leaking the other way: 0 wrongly
#                                 flagged active, 82 wrongly hidden.
#
#                                 The column is DEFINED as
#                                 in_custody || currently_in_exile by
#                                 the model saving hook, so this needs no
#                                 research and no judgement -- any row
#                                 that disagrees is wrong by
#                                 construction. It repairs both
#                                 directions and verifies itself.
#
# NOT IN THIS BATCH: the ten records flagged BOTH in custody and
# released. Those are contradictions in the source data rather than a
# stale derivation, so they are being researched one at a time.
#
# NO PLACEMENT OR RECOMPUTE TAIL. One boolean column changes; records
# already correct are not saved at all.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-26.sh

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
echo "  Batch 26 — resync imprisoned_or_exiled"
echo "==================================================================="

run "resync-imprisoned-or-exiled" bash database/data/resync-imprisoned-or-exiled.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 26 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
