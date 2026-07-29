#!/usr/bin/env bash
#
# BATCH 17 -- Haniffa bin Osman was described as his co-defendant.
#
#   fix-haniffa-bin-osman   Both Osman and Erick Wotulo carried the
#                           descriptor "retired Indonesian Marine Corps
#                           general". It is Wotulo{39}s. Osman was a
#                           Singaporean civilian arms broker, and his
#                           description is rewritten from the DOJ
#                           record of the same 2006 LTTE sting --
#                           ending by naming Wotulo as the general, so
#                           the two records cannot swap identities
#                           again. Wotulo is left untouched.
#
# NO PLACEMENT OR RECOMPUTE TAIL. Nothing is created, moved, or dated.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-17.sh

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
echo "  Batch 17 — Haniffa bin Osman identity correction"
echo "==================================================================="

run "fix-haniffa-bin-osman" bash database/data/fix-haniffa-bin-osman.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 17 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
