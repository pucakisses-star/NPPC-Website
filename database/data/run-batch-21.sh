#!/usr/bin/env bash
#
# BATCH 21 -- Frank J. Muscare custody dates.
#
#   fix-frank-muscare   His case row carried no dates at all, so a man
#                       who went to Cook County Jail showed nothing
#                       served. Fills the custody (1980-02-21 to
#                       1980-03-14), the institution, the judge, his
#                       middle initial and AKA, and his life dates
#                       (1925-11-08 to 2005-02-28). Also records WHY he
#                       served only 23 days of a five-month contempt
#                       sentence -- a \$100,000 appeal bond, not a purge
#                       and not time served -- and that whether he ever
#                       returned to finish it is unresolved.
#
# NO PLACEMENT TAIL. The record already exists and is not moved.
#
# The case row is saved directly, so the day counter recomputes itself;
# no recompute pass needed.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-21.sh

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
echo "  Batch 21 — Frank J. Muscare"
echo "==================================================================="

run "fix-frank-muscare" bash database/data/fix-frank-muscare.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 21 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
