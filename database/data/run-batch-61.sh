#!/usr/bin/env bash
#
# BATCH 61 -- the CRDL mining cohort: the 1961 Jackson Freedom Riders
# and sit-in arrestees, the Americus Four pair, the Albany eight's
# leaders, and the Greensboro 1963 detainee — seventy-two new records,
# eight enriched, and the Sovereignty Commission mugshots.
#
#   fix-crdl-freedom-riders
#
#     Every record holds the curator's evidentiary line: CRDL proves
#     the arrest and the individually captioned identification
#     photograph, NOT the disposition, a Parchman transfer, or a
#     release date — so dispositions read unresolved, releases stay
#     empty, and nobody is assigned the generic Freedom Rider custody
#     path. Documented exceptions only: Janice Rogers at Parchman
#     (where the catalog reports she miscarried), Peter Stoner's 1964
#     Forrest County work camp, and the Americus pair's no-bail
#     August 8 to November 1, 1963 confinement.
#
#     Existing records enriched, never clobbered: Braden's 1954
#     sedition case, Roodenko's 1947 chain gang, Walker's third case
#     row + vitals + mugshot, Felix Singer's arrest + mugshot, Forman
#     and Bernard Lee's Albany arrests, Lewis's arrest tightened to
#     the placard date (5-24-61), and Diane Nash's impossible
#     release-before-arrest cleared.
#
#     Photos: the individually captioned Sovereignty Commission
#     mugshots (CREDITS-crdl-freedom-riders.md), frontal panels at
#     525x700; the attach loop only fills empty slots.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-61.sh

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
echo "  Batch 61 — the CRDL mining cohort"
echo "==================================================================="

run "fix-crdl-freedom-riders" bash database/data/fix-crdl-freedom-riders.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 61 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
