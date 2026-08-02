#!/usr/bin/env bash
#
# BATCH 98 -- the Robert Malecki obituary article.
#
#   Publishes the obituary for Robert Malecki (October 27, 1942 -
#   September 24, 2024) via the dedicated command
#   articles:add-malecki-obituary: the draft-card destruction of
#   1968-1972, the Dow Chemical computer-network action, the prison
#   term, the bomb-conspiracy charge that followed his release, the
#   June 1972 flight to Sweden funded by fellow anti-war protesters,
#   and the fifty-two years of exile that ended with his death.
#
#   Filed under News, byline NPPC Editorial; his database portrait is
#   reused as the article image when present. Idempotent — re-running
#   updates the article in place by slug.
#
#   Live at /news/robert-malecki-obituary-1942-2024 after the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-98.sh

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
echo "  Batch 98 — Robert Malecki obituary article"
echo "==================================================================="

run "add-malecki-obituary" php artisan articles:add-malecki-obituary

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 98 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
