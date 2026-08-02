#!/usr/bin/env bash
#
# BATCH 102 -- the Malecki obituary re-dated to his death, and the
# Johanna Fernández obituary published.
#
#   ROBERT MALECKI — the batch 98 obituary article now carries
#   published_at = SEPTEMBER 24, 2024, his actual death date, per the
#   curator. Re-running articles:add-malecki-obituary updates the
#   article in place (idempotent by slug).
#
#   JOHANNA FERNÁNDEZ (1970-2026) — new obituary via
#   articles:add-fernandez-obituary, dated to her death (July 30,
#   2026): the Baruch College historian of the Young Lords, the FOIA
#   litigation that surfaced a million pages of NYPD surveillance
#   files, her two decades of advocacy for Mumia Abu-Jamal, and her
#   own 1992 arrest among the 253 at Brown-s University Hall
#   occupation.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-102.sh

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
echo "  Batch 102 — Malecki article re-dated; Fernández obituary"
echo "==================================================================="

run "redate-malecki-obituary" php artisan articles:add-malecki-obituary
run "add-fernandez-obituary" php artisan articles:add-fernandez-obituary

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 102 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
