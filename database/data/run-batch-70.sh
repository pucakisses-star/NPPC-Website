#!/usr/bin/env bash
#
# BATCH 70 -- Willie Roger Holder: the 1991-92 parole-violation
# jailing, and the curator-s replacement biography.
#
#   Rearrested July 2, 1991 on a parole violation — a police informant
#   claimed he was planning another hijacking — and held until June 2,
#   1992, his final release from prison. The bio is replaced verbatim
#   with the curator-s fuller text, and the US air-piracy row-s
#   sentence text gains the four-year term and the August 1989 parole.
#   Details in fix-holder-parole-case.sh.
#
# Run from the repo root, after git pull (batch 69 must have run):
#   bash database/data/run-batch-70.sh

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
echo "  Batch 70 — Holder: 1991-92 parole-violation jailing + bio"
echo "==================================================================="

run "fix-holder-parole-case" bash database/data/fix-holder-parole-case.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 70 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
