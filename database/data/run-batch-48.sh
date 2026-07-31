#!/usr/bin/env bash
#
# BATCH 48 -- Thomas Mooney's triple case row, and the two Ed Burnses.
#
#   fix-mooney-and-the-two-burns
#
#     THOMAS MOONEY had one imprisonment and THREE case rows — the same
#     off-by-one double import as Billings, plus a dateless third. Two
#     rows go. The survivor is corrected: arrest and custody at month
#     precision (July 1916 — the rows and the accounts disagree on the
#     day), sentenced February 24, 1917 in the sentenced_date field,
#     release January 7, 1939, the day of Olson's pardon. His custody
#     previously counted from February 1917, the sentencing, though he
#     was held without bail from arrest — the counter started seven
#     months late. The institution moves off "San Quentin Rehabilitation
#     Center", the prison's name since 2023, onto San Quentin State
#     Prison.
#
#     ED BURNS (Sacramento) died in the county jail in November 1918;
#     his record carried an arrest date of February 1924 — five years
#     after his death — and NO death date. The impossible arrest is
#     removed and nothing replaces it (his real arrest date is
#     undocumented); he gains the death date, a death_in_custody_date
#     that ends his year counter at his death, died-in-custody flags,
#     the Sacramento County Jail, and a biography that says which Ed
#     Burns he is.
#
#     ED BURNS (Seattle) — NEW RECORD, ed-burns-seattle — is W. E.
#     Spear's assistant, arrested in the April 22, 1920 Globe Building
#     raid with Spear and Alicia Rosenbaum. Batch 47 created his two
#     co-defendants and could only name him in their texts; this
#     completes the trio, under the same evidentiary limits: no release
#     date (custody span undocumented), disposition unresolved.
#
# PLACEMENT TAIL: Sacramento County Jail may be created.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-48.sh

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
echo "  Batch 48 — Mooney's case rows, and the two Ed Burnses"
echo "==================================================================="

run "fix-mooney-and-the-two-burns" bash database/data/fix-mooney-and-the-two-burns.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 48 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
