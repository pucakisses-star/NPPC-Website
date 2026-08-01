#!/usr/bin/env bash
#
# BATCH 56 -- the two Larry Jacksons: Karim Njabafudi split from
# John England Morris Jr.
#
#   fix-njabafudi-morris
#
#     A major identity correction. The larry-jackson record conflated
#     two men who shared that alias: KARIM HEKIMA OMAN WADU NJABAFUDI
#     — born Larry Jackson in New Orleans, about fifteen when arrested
#     with the RNA-11 in the August 18, 1971 Jackson, Mississippi raid
#     — and JOHN ENGLAND MORRIS JR., the adult "Larry Jackson" of the
#     November 26, 1970 New Orleans Desire-project Panther raid.
#
#     KARIM keeps the karim record: name corrected from Njabafundi
#     (slug regenerates — the Herkner rule for a demonstrable
#     misspelling), circa-1955 birth year with no exact date inferred,
#     and the full custody record his thin row lacked — arrest
#     August 18, 1971, life sentence September 25, 1972 as an aider
#     and abettor (the fatal shot was attributed to Hekima Ana),
#     affirmed 1975, released November 1979 at month precision, at
#     Parchman. The claimed federal fifteen-year term stays out: he
#     was dismissed from the adult federal case as an unwaived
#     juvenile and no judgment proving a juvenile prosecution has been
#     located.
#
#     MORRIS keeps the larry-jackson record, renamed. The RNA case row
#     is DELETED from it — with its wrong August 17 arrest, its
#     "Mississippi state and federal courts" institution, its stray
#     FCI Marianna address and its invented "2-10 years" sentence —
#     and the RNA affiliation comes off. His documented Desire case
#     stays: arrest November 26, 1970, Orleans Parish Prison,
#     conviction for possessing an unregistered automatic rifle,
#     custody span honestly unresolved with no release entered.
#
#     NO PHOTO: the only authenticated image is the AP group photo of
#     the eleven chained defendants, with no per-figure caption to
#     anchor a crop. Both slugs are pre-listed for drop-in completion.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-56.sh

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
echo "  Batch 56 — the two Larry Jacksons, split"
echo "==================================================================="

run "fix-njabafudi-morris" bash database/data/fix-njabafudi-morris.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 56 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
