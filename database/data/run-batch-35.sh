#!/usr/bin/env bash
#
# BATCH 35 -- two acquitted men recorded as convicted, and the model bug
# that turned their missing release dates into decades in prison.
#
#   fix-smith-act-acquittals-1   Isidore Begun and Simon W. Gerson were
#                                described as convicted in the 1952
#                                Foley Square Smith Act trial and
#                                sentenced to two and three years. Judge
#                                Edward J. Dimock DIRECTED VERDICTS OF
#                                ACQUITTAL for both on September 30,
#                                1952. Neither was convicted; neither
#                                served a sentence.
#
#                                A previous partial fix had set the
#                                Convicted field to "No — acquitted" but
#                                left the biography asserting the
#                                conviction and the sentence field
#                                opening with "2 years —" and "3 years
#                                —". The visible text still said prison.
#
# ALSO IN THIS BATCH, as a code change rather than a script:
# Prisoner::getIncarcerationYearsArray fell back to the prisoner death
# date for a RELEASED prisoner with no recorded release date, which did
# not cap the range but extended it across the rest of their life. Gerson,
# jailed about ten days in 1951 and acquitted, was listed as imprisoned
# every year from 1951 to his death in 2004. 63 records were affected and
# 2,250 fabricated prisoner-years come out of the stats chart.
#
# A RECOMPUTE IS INCLUDED because the years array is derived on read but
# the day counters are stored.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-35.sh

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
echo "  Batch 35 — Smith Act acquittals"
echo "==================================================================="

run "fix-smith-act-acquittals-1" bash database/data/fix-smith-act-acquittals-1.sh
run "recompute-imprisonment" php artisan prisoners:recompute-imprisonment --apply

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 35 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
