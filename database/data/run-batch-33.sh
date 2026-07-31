#!/usr/bin/env bash
#
# BATCH 33 -- six curator corrections to current-era records.
#
#   fix-modern-case-corrections-1
#
#     kat-abughazaleh   Was NOT arrested at the September 26, 2025
#                       demonstration. She self-surrendered on November
#                       12, 2025 and was released the same day without
#                       being jailed. Arrest date moves; no custody is
#                       recorded because there was none.
#     larry-bushart     Arrested late September 21, not September 20.
#                       Counter 37 -> 36 days.
#     lucas-griffith    Released July 18, 2025. His row recorded no
#                       custody at all; he was held overnight.
#     mahmoud-khalil    Arrested March 8, not March 7. Counter 104 -> 103.
#     mohammed-hoque    Was 20 when first detained, not 22.
#     john-wade         Federal release June 2024, REARRESTED October
#                       2024, still in custody spring 2026. His record
#                       showed a single case releasing him in November
#                       2025 while flagging him in custody, so the
#                       counter read zero. It now reads about 668 days.
#
# THE BIGGEST CHANGE IS JOHN WADE. A man listed as in custody was
# reporting no time served at all. He gains a second case row for the
# rearrest.
#
# NO PLACEMENT TAIL. Nothing is created except one case row on an
# existing person.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-33.sh

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
echo "  Batch 33 — current-era case corrections"
echo "==================================================================="

run "fix-modern-case-corrections-1" bash database/data/fix-modern-case-corrections-1.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 33 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
