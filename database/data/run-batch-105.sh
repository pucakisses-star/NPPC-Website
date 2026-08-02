#!/usr/bin/env bash
#
# BATCH 105 -- the CRDL mining cohort, third wave: eleven new 1961
# Jackson Freedom Riders (the June 20 Illinois Central group, June 23,
# and June 25), Miriam Feingold's mugshot, Wahlstrom's second arrest,
# and the Teale/Muntean authority-name corrections.
#
#   fix-crdl-freedom-riders-3
#
#     Runs AFTER batch 103 (it enriches records that batch creates).
#     Every direct CRDL record id re-verified against the live
#     catalog; placards read against ledger dates on a contact sheet.
#     Photos from the MDAH large scans, frontal panels at 525x700;
#     the attach loop only fills empty slots, and birth dates enter
#     only where the field is empty.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-105.sh

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
echo "  Batch 105 — the CRDL mining cohort, third wave"
echo "==================================================================="

run "fix-crdl-freedom-riders-3" bash database/data/fix-crdl-freedom-riders-3.sh
run "sort-new-placement" php artisan prisoners:sort-new

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 105 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
