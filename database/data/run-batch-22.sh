#!/usr/bin/env bash
#
# BATCH 22 -- Salvatore Accorsi.
#
#   fix-salvatore-accorsi   His record stopped at "facing the electric
#                           chair" and never said he was acquitted. Sets
#                           life dates (1897-09-23 to 1945-02-19),
#                           upgrades the bare 1929 arrest to June 12,
#                           adds the custody (1929-06-12 to 1929-12-13,
#                           Allegheny County Jail), records the verdict
#                           as No -- acquitted, moves the narrative out
#                           of the charges field, and rewrites the
#                           description to carry the whole arc through
#                           to the December 13, 1929 not-guilty verdict.
#
# NO PLACEMENT TAIL. The record already exists and is not moved. The
# case row is saved directly, so the day counter recomputes itself.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-22.sh

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
echo "  Batch 22 — Salvatore Accorsi"
echo "==================================================================="

run "fix-salvatore-accorsi" bash database/data/fix-salvatore-accorsi.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 22 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
