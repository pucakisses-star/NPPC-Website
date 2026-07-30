#!/usr/bin/env bash
#
# BATCH 30 -- sixteen Silent Sentinels get their identities and dates.
#
#   fix-suffragist-identities-1   Applies a researcher dossier to sixteen
#                                 National Woman’s Party records. Most
#                                 were built from Doris Stevens’s
#                                 appendix, which gives a name and a
#                                 sentence length and nothing else: of
#                                 the 143 records in the cohort, six had
#                                 a birthdate.
#
#                                 Two names were wrong in the database:
#                                 “Anne Herkimer” is Anna Herkner, the
#                                 Baltimore child-labor inspector, and
#                                 “Purtelle” is Purtell. BOTH SLUGS
#                                 CHANGE.
#
#                                 One record claimed a prison term that
#                                 never happened: Dr. Sarah Hunt Lockrey
#                                 paid the fine so she could operate.
#
#                                 Two identities are deliberately left
#                                 unresolved rather than merged, and one
#                                 case row that had Julia Emory jailed
#                                 two months before her arrest is fixed.
#
# NO PLACEMENT TAIL. No records are created; the new rows are case rows
# on people who already exist.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-30.sh

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
echo "  Batch 30 — Silent Sentinel identities and lifespans"
echo "==================================================================="

run "fix-suffragist-identities-1" bash database/data/fix-suffragist-identities-1.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 30 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
