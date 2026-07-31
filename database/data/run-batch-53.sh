#!/usr/bin/env bash
#
# BATCH 53 -- Aurelio Luis Perez-Lugones is no longer jailed.
#
#   fix-perez-lugones
#
#     Released May 4, 2026 by Judge Matthew J. Maddox (miscalled Michael
#     in several reports) to strict pretrial home detention with
#     electronic monitoring and device restrictions. His record listed
#     him as presently incarcerated with a running counter; it now ends
#     at 116 days, January 8 to May 4, 2026. Flags:
#     released-pending-trial.
#
#     Also corrected: the prosecution is in the DISTRICT OF MARYLAND,
#     not E.D. Va. (that is the separate device-unsealing litigation);
#     the Alexandria Detention Center link is REMOVED because the actual
#     facility is not reliably identified; plea is not guilty; age moves
#     to 61 as reported at arrest; race is CLEARED as undocumented and
#     name-inferred; and the biography keeps the Reporter 1 and
#     Venezuela identifications phrased as allegations and attributions.
#
#     Photo: the FBI surveillance still from page 33 of the unsealed
#     warrant affidavit, timestamped January 6, 2026 — the affidavit
#     itself identifies the figure. Degraded photocopy quality; replace
#     if a better-sourced portrait surfaces.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-53.sh

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
echo "  Batch 53 — Perez-Lugones: released May 4, 2026"
echo "==================================================================="

run "fix-perez-lugones" bash database/data/fix-perez-lugones.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 53 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
