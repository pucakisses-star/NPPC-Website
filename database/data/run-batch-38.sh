#!/usr/bin/env bash
#
# BATCH 38 -- Dr. Otis Wesley Smith served the seven days.
#
#   fix-otis-wesley-smith   The record described an eight-month work-gang
#                           sentence "suspended only on condition that he
#                           write the woman a letter begging forgiveness
#                           and leave town" -- which reads as though he
#                           served nothing. HE SERVED SEVEN DAYS, March
#                           10 to 17, 1958.
#
#                           The telephone incident was June 25, 1957, not
#                           1958. The $500 fine came with the March 17
#                           modification, not the original sentence. NO
#                           RELIABLE SUPPORT was found for the letter
#                           begging forgiveness, which is removed.
#
#                           THE SLUG CHANGES: otis-w-smith becomes
#                           otis-wesley-smith.
#
#                           His portrait comes from his own funeral
#                           program in the Digital Library of Georgia,
#                           whose cover supplies both vital dates.
#
# A RECOMPUTE IS INCLUDED because the case gains a closed custody span.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-38.sh

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
echo "  Batch 38 — Dr. Otis Wesley Smith"
echo "==================================================================="

run "fix-otis-wesley-smith" bash database/data/fix-otis-wesley-smith.sh
run "recompute-imprisonment" php artisan prisoners:recompute-imprisonment --apply

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 38 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
