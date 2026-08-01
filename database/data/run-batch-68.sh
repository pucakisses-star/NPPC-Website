#!/usr/bin/env bash
#
# BATCH 68 -- Catherine Marie Kerkow: the 1975 French custody, entered
# as a second case row.
#
#   Arrested with Willie Roger Holder in Paris January 25, 1975; held
#   at Fleury-Mérogis Prison while France considered the American
#   extradition request (refused April 7, 1975 as politically
#   motivated); convicted June 2, 1975 of presenting a falsified
#   passport — three months and one day plus a 1,000-franc fine — and
#   reported by Le Monde as due for release because her ~128 days of
#   detention had already exceeded the sentence. Judicial supervision,
#   not custody, under the French hijacking prosecution that followed.
#
#   The row pins a zero-length exile pair so the summed exile counter
#   stays solely on the 1972 air-piracy case (in exile since June 2,
#   1972, per batch 67) — details in fix-kerkow-french-custody.sh.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-68.sh

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
echo "  Batch 68 — Kerkow: the 1975 French custody case"
echo "==================================================================="

run "fix-kerkow-french-custody" bash database/data/fix-kerkow-french-custody.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 68 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
