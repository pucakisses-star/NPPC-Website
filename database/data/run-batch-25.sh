#!/usr/bin/env bash
#
# BATCH 25 -- portraits for the two 1865 governors.
#
#   attach-civil-war-governor-photos   Brady-Handy portrait of Zebulon
#                                      Vance (Library of Congress) and
#                                      Brady portrait of John Letcher
#                                      (National Archives 528418). Both
#                                      public domain; provenance in
#                                      database/data/photos/CREDITS-civil-war-governors.md
#
# RUN BATCH 24 FIRST. Letcher is created there. If he does not exist yet
# this still attaches the Vance portrait and reports him missing.
#
# NO PLACEMENT OR RECOMPUTE TAIL. Only the photo field changes.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-25.sh

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
echo "  Batch 25 — Vance and Letcher portraits"
echo "==================================================================="

run "attach-civil-war-governor-photos" bash database/data/attach-civil-war-governor-photos.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 25 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
