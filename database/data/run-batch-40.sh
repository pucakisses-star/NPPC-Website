#!/usr/bin/env bash
#
# BATCH 40 -- Reverend Samuel Green was never tried for the Dover Eight.
#
#   fix-samuel-green   The record had the two trials the wrong way round:
#                      it said he was tried and acquitted for aiding the
#                      Dover Eight escape and then charged over the book.
#                      He was NEVER TRIED for the escape -- the state's
#                      attorney found insufficient evidence and never
#                      brought it. Both indictments were for possessing
#                      prohibited abolitionist material, and the acquittal
#                      was on the first of those: a letter from Canada, a
#                      map and railroad schedules.
#
#                      Two case rows now, one per indictment. The
#                      acquitted row carries the arrest and no custody;
#                      the conviction carries the 1,799 days.
#
#                      Vital dates, prisoner number 5146, the Maryland
#                      Penitentiary, the sentencing date and Governor
#                      Bradford's conditional pardon are all added.
#
# A RECOMPUTE IS INCLUDED because the conviction gains a closed span.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-40.sh

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
echo "  Batch 40 — Reverend Samuel Green"
echo "==================================================================="

run "fix-samuel-green" bash database/data/fix-samuel-green.sh
run "recompute-imprisonment" php artisan prisoners:recompute-imprisonment --apply

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 40 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
