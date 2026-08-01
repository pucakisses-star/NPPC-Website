#!/usr/bin/env bash
#
# BATCH 69 -- Willie Roger Holder: new record for Catherine Marie
# Kerkow-s co-hijacker, with his Vietnam-era photograph.
#
#   DOB June 14, 1949; died February 6, 2012; buried at Fort Rosecrans
#   National Cemetery, San Diego. Three case rows: the 1972 hijacking
#   (exile June 3, 1972 - July 26, 1986, then US custody to August
#   1989), the 1975 French custody at Fleury-Mérogis (~128 days, three
#   months and fifteen days for falsified passports, released for time
#   served), and the 1980 French hijacking conviction (five years,
#   suspended). Details in fix-willie-roger-holder.sh.
#
#   The final step re-runs prisoners:sort-new so the new record takes
#   a dated place in the roster instead of sort_order 0 (no-op for
#   everything already placed).
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-69.sh

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
echo "  Batch 69 — Willie Roger Holder: new record + photo"
echo "==================================================================="

run "fix-willie-roger-holder" bash database/data/fix-willie-roger-holder.sh
run "sort-new-placement" php artisan prisoners:sort-new

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 69 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
