#!/usr/bin/env bash
#
# BATCH 20 -- derived birth years, round 2.
#
#   fix-birthyears-derived-2   31 of 36 researched names dated: five
#                              full stated dates (Priscilla Grim, plus
#                              four from a Riverside County DA release
#                              that publishes DOBs outright), eight
#                              years pinned by intersecting dated ages,
#                              and eighteen stored circa. Five stay
#                              null -- one on irreconcilable reported
#                              ages, four Akron arrestees whose ages
#                              appear only on sites this project will
#                              not use.
#
#                              Needs the circa precision. Pull first.
#
# NO PLACEMENT OR RECOMPUTE TAIL. Nothing created or moved, no case
# dates touched; ages recompute from the birthdate on save.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-20.sh

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
echo "  Batch 20 — derived birth years, round 2"
echo "==================================================================="

run "fix-birthyears-derived-2" bash database/data/fix-birthyears-derived-2.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 20 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
