#!/usr/bin/env bash
#
# BATCH 9 -- researched birthdates, round 6.
#
#   fix-birthdates-researched-6   twelve records get source-stated
#                                 dates out of 36 names researched --
#                                 the best round so far: Pascale
#                                 Ferrier (her own sworn statement),
#                                 Terrence Johnson, Irv Rubin, Twymon
#                                 Myers, Barry Bondhus, Anni Rainbow,
#                                 Bruce Dancis (Library of Congress),
#                                 James E. Robinson (FBI affidavit),
#                                 and birth years for William F.
#                                 Kruse, Mourad Topalian, Michael
#                                 Cullen and Vaun L. Mayes. Sources
#                                 in the script header. Twenty-four
#                                 names stay null.
#
# NO PLACEMENT OR RECOMPUTE TAIL. Nothing here creates a record, moves
# one, or touches a case date.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-9.sh

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
echo "  Batch 9 — researched birthdates, round 6"
echo "==================================================================="

run "fix-birthdates-researched-6" bash database/data/fix-birthdates-researched-6.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 9 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
