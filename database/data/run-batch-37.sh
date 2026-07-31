#!/usr/bin/env bash
#
# BATCH 37 -- twelve records asserting more than their sources support.
#
#   fix-misclassified-prosecutions-2
#
#     Three renames, all changing the slug: Ed Evans is Henry C. Evans,
#     Charles Jacobsen is C. Jacobsen, Prisciliano Silva is Prisciliano
#     G. Silva.
#
#     Four over-assertions withdrawn: Espinosa's sentence range was
#     Turner's GROUP figure; Boutin's "guilty" rested on a PRETRIAL
#     ruling; Johnson's ten days come from a report naming nobody;
#     O'Leary's San Patricio group punishments cannot be assigned to him
#     individually.
#
#     One impossible ideology removed: Santiago O'Leary, captured at
#     Churubusco in 1847, was tagged Catholic Worker Movement -- founded
#     1933.
#
#     Two jurisdictional fixes: Albert Brooks under Montana's state
#     sedition law, with the 1920 Supreme Court reversal recorded; Walter
#     Nichiperuck as an administrative immigration deportee.
#
# NO RELEASE DATE IS PROJECTED FROM A SENTENCE LENGTH anywhere in this
# batch, so several records deliberately show no time-served figure.
#
# A RECOMPUTE IS INCLUDED because one record gains a death in custody,
# which the hook mirrors onto the release date.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-37.sh

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
echo "  Batch 37 — over-asserted and misclassified records"
echo "==================================================================="

run "fix-misclassified-prosecutions-2" bash database/data/fix-misclassified-prosecutions-2.sh
run "recompute-imprisonment" php artisan prisoners:recompute-imprisonment --apply

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 37 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
