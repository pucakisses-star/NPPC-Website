#!/usr/bin/env bash
#
# BATCH 60 -- Jeff Hogg: the truncated biography completed.
#
#   fix-jeff-hogg  (extended)
#
#     His stored description was a cut-off fragment — "Jeff Hogg was
#     an Oregon environmental activist and nursing student" — ending
#     mid-sentence. The standalone fix script that already corrected
#     his dates, charge, institution and affiliation (PRs #2041/#2042,
#     long since applied on the server) now also replaces that
#     fragment with a full biography: the Eugene grand jury
#     investigating the Operation Backfire arson prosecutions, his
#     refusal to testify, the 181 days for civil contempt at the
#     Josephine County Jail (May 18 to November 15, 2006) making him
#     the longest-jailed grand jury resister of the Green Scare era,
#     the release when the court concluded confinement would not
#     coerce him, and the fact that he was never charged with any
#     crime.
#
#     The description update only fires when the known fragment (or an
#     empty bio) is found, so a curator-edited biography is never
#     overwritten; every other step of the script is already
#     idempotent and re-runs harmlessly.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-60.sh

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
echo "  Batch 60 — Jeff Hogg: biography completed"
echo "==================================================================="

run "fix-jeff-hogg" bash database/data/fix-jeff-hogg.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 60 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
