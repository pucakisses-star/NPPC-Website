#!/usr/bin/env bash
#
# BATCH 58 -- the Piety Street defendants: verified findings, seven
# captioned portraits, and the two missing members of the roster.
#
#   fix-piety-street
#
#     THE COHORT (New Orleans Panthers, arrested September 15, 1970
#     after the police assault on the Piety Street headquarters, all
#     acquitted August 6, 1971) gains its documented custody: arrest
#     and incarceration September 15, 1970, Orleans Parish Prison,
#     and — on the curator-s standing instruction — the acquittal
#     date ASSUMED as the release date unless implied otherwise,
#     with the assumption stated in every sentence text. Implied
#     otherwise, so no release: Ronald Ailsworth (federal and New
#     Haven holds; he also gains a second case row for the unrelated
#     Angola imprisonment that ended September 26, 2019 after
#     approximately forty years) and Charles Rudolph Scott (the same
#     reported holds; back in Queens only by about mid-1972).
#
#     BIRTH YEARS derived from age at arrest at circa precision (the
#     Camden 28 method); exact dates only where documented — Alton
#     Edwards d. 2020-01-26 (obituary identifying his codefendant
#     brother), Charles Rudolph Scott d. 1999-07-21.
#
#     CREATED: Charles Rudolph Scott (charles-rudolph-scott — the
#     plain slug belongs to a California man) and Catherine Bournes,
#     the two defendants missing from the database.
#
#     PORTRAITS: seven attached from the curator-supplied composite
#     sheet of period newspaper cuts, each anchored by its own
#     printed caption (see CREDITS-piety-street.md). The 1970 Guyton
#     portrait is stored as malik-rahim-1970.jpg but NOT attached —
#     the record already has a photo, and the attach loop only fills
#     empty slots.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-58.sh

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
echo "  Batch 58 — the Piety Street defendants"
echo "==================================================================="

run "fix-piety-street" bash database/data/fix-piety-street.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 58 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
