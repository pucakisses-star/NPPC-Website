#!/usr/bin/env bash
#
# BATCH 103 -- the CRDL mining cohort, second wave: the remaining
# 1961 Jackson Freedom Riders and sit-in arrestees, the Episcopal
# clergy ride behind Pierson v. Ray, the Albany train-station
# arrests, and one 1954 literature arrest.
#
#   fix-crdl-freedom-riders-2
#
#     Every direct CRDL record id re-verified against the live
#     catalog before import; every record holds the first wave's
#     evidentiary line (CRDL proves the arrest and the photograph,
#     not the disposition or release). Documented custody only for
#     Canty, Hickerson, Baer, Helen Singleton, and the Episcopal
#     clergy's September 15 conviction. Sovereignty Commission
#     mugshots from the MDAH large scans, frontal panels at 525x700;
#     the attach loop only fills empty slots.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-103.sh

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
echo "  Batch 103 — the CRDL mining cohort, second wave"
echo "==================================================================="

run "fix-crdl-freedom-riders-2" bash database/data/fix-crdl-freedom-riders-2.sh
run "sort-new-placement" php artisan prisoners:sort-new

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 103 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
