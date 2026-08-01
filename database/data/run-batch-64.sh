#!/usr/bin/env bash
#
# BATCH 64 -- the Ridglan Farms defendants' two jailings split, and
# Gabriela Saldana's booking corrected.
#
#   fix-ridglan-two-jailings
#
#     ASWANI, WYRZYKOWSKI, LUNSKY: one prosecution, two custody
#     episodes, previously conflated into one row. Case 1 becomes the
#     March 15, 2026 rescue detention (released without charges:
#     Aswani and Wyrzykowski March 17; Lunsky by March 16 at
#     approximate precision). A NEW case 2 carries the April 18-21,
#     2026 return to jail on the four felony counts, the $10,000
#     bond, the May 21 not-guilty plea and the September 28 trial —
#     which all belonged to that second custody. All three become
#     released-on-bond with the minor_case duration flag.
#
#     SALDANA: arrest corrected April 15 -> April 16, 2026 (booking
#     record: arrested shortly after 2:10 a.m., booked 4:15 a.m.);
#     institution corrected to the Turner Guilford Knight
#     Correctional Center; released on the $5,000 bond by April 20
#     (approximate precision, discharge timestamp not established).
#
# Run from the repo root, after git pull (then re-run
# php artisan prisoners:sort-new if batch 62 has been applied):
#   bash database/data/run-batch-64.sh

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
echo "  Batch 64 — Ridglan two jailings; Saldana booking corrected"
echo "==================================================================="

run "fix-ridglan-two-jailings" bash database/data/fix-ridglan-two-jailings.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 64 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
