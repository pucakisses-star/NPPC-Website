#!/usr/bin/env bash
#
# BATCH 27 -- the ten in-custody/released contradictions.
#
#   fix-custody-flag-conflicts   Researched one at a time, because the
#                                flags contradict each other in the
#                                source data and no rule says which half
#                                is wrong. They came back as FOUR
#                                different states:
#
#                                  still inside (3) -- Fred Burton at SCI
#                                    Chester, Alvaro Hernandez on a life
#                                    sentence at Estelle, Haki Malik
#                                    Abdullah at Folsom
#                                  released (5) -- Espinosa-Villegas,
#                                    Augustyniak-Duncan, McKay, Remiro,
#                                    Conway
#                                  died in custody (2) -- Luis V.
#                                    Rodriguez 2016-04-14 and Romaine
#                                    Fitzgerald 2021-03-29, never
#                                    released
#                                  died after release (1) -- Conway,
#                                    2023-02-13, nearly nine years out
#
#                                Three deaths were entirely absent from
#                                the database before this.
#
# RUN BATCH 26 FIRST if it has not been applied. It resyncs the derived
# imprisoned_or_exiled column; this batch changes in_custody on three
# records, and the derivation runs on save, so order does not strictly
# matter -- but 26 fixes the other 82 rows this one does not touch.
#
# NO PLACEMENT TAIL. Nothing is created or moved. Case rows are saved
# directly where a release date is added, so day counters recompute.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-27.sh

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
echo "  Batch 27 — in-custody/released contradictions"
echo "==================================================================="

run "fix-custody-flag-conflicts" bash database/data/fix-custody-flag-conflicts.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 27 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
