#!/usr/bin/env bash
#
# BATCH 23 -- bio typo audit, round 1.
#
#   fix-bio-typos-1   82 corrections across 70 records: OCR hyphenation
#                     artifacts ("con- spiracy", "sen- tenced"), lost
#                     spaces ("andsentenced", "whenhe", "onbond"), plain
#                     misspellings ("recieve", "durnig", "transfered"),
#                     and two invisible-character bugs -- soft hyphens
#                     inside a word, and a mojibake u-umlaut standing in
#                     for an opening quote.
#
#                     British spellings, accented and foreign words,
#                     modern vocabulary, quoted archival misspellings
#                     and dialect are deliberately left alone; the
#                     reasoning is in the script header.
#
# NO PLACEMENT OR RECOMPUTE TAIL. Only description text changes.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-23.sh

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
echo "  Batch 23 — bio typo audit, round 1"
echo "==================================================================="

run "fix-bio-typos-1" bash database/data/fix-bio-typos-1.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 23 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
