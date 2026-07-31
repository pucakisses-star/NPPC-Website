#!/usr/bin/env bash
#
# BATCH 34 -- twenty-five more Silent Sentinels, dossier entries 26-50.
#
#   fix-suffragist-identities-3   Lifespans and documented custody for
#                                 twenty-five records, EIGHT RENAMES
#                                 (five filed under a husband's name,
#                                 three misspellings) and three stored
#                                 date errors corrected.
#
#                                 ALL EIGHT RENAMED SLUGS CHANGE.
#
# THE JULY 1917 THREE had custody running 1917-07-17 to 07-19 -- starting
# on the day they were pardoned. Arrested July 14, pardoned July 17.
#
# TWO had the wrong month: Ada Kendall and Bertha Jackson were arrested
# September 13, 1917, not in August.
#
# NO PLACEMENT TAIL. Nothing is created; new rows are case rows on people
# who already exist.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-34.sh

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
echo "  Batch 34 — Silent Sentinels, dossier entries 26-50"
echo "==================================================================="

run "fix-suffragist-identities-3" bash database/data/fix-suffragist-identities-3.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 34 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
