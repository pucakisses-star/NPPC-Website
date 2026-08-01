#!/usr/bin/env bash
#
# BATCH 57 -- Spencer Anderson: booking photo, and the record goes
# under review.
#
#   fix-spencer-anderson
#
#     THE PHOTO: the booking photograph FOX 2 Detroit published
#     (supplied by Waterford Police; WDIV credits the Oakland County
#     Jail) — a high-confidence identification, cropped from the FOX 2
#     letterboxed web image to the 525x700 convention.
#
#     UNDER REVIEW: the verified record supports at most one night of
#     arrest-and-booking custody — arrested February 26, 2026,
#     arraigned before Judge Todd Fox in the 51st District Court and
#     released February 27 on a $500 cash bond — with no evidence of a
#     jail sentence or further pretrial detention, and no reliable
#     docket establishing the current disposition (the March 2026
#     hearing dates were scheduled, then rescheduled). The record is
#     hidden from the public site until the case resolves; Filament
#     still shows it.
#
#     Also: custody span entered (Feb 26-27, one day, minor_case
#     duration flag), age 25 -> 24 as reported at arrest, the
#     curator's recommended biography verbatim — including the
#     correction that no statement from Anderson documents his
#     political motive — and the per-count (not stacked) statutory
#     maximum in the sentence text.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-57.sh

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
echo "  Batch 57 — Spencer Anderson: photo attached, record under review"
echo "==================================================================="

run "fix-spencer-anderson" bash database/data/fix-spencer-anderson.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 57 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
