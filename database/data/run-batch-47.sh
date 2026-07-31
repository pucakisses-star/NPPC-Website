#!/usr/bin/env bash
#
# BATCH 47 -- the University of Washington IWW list.
#
#   add-uw-iww-arrestees
#
#     Fourteen curator-supplied names resolve into TWELVE NEW RECORDS,
#     TWO CORRECTIONS, and one name that must not become a record:
#
#     New: Max Dezettel, Jennie La Zar, Walker C. Smith, Peter Lynch,
#     James Cronin, James Hayes, W. E. Spear, Alicia Rosenbaum, James
#     Schmidt, James Johnson (Everett), George Bradley, Louis Lavine.
#
#     "ALICE ROSE" IS AN ALIAS OF ALICIA ROSENBAUM and enters as her
#     AKA, alongside Alice Lloyd. No Alice Rose record is created.
#
#     WARREN BILLINGS existed already, with the same imprisonment
#     imported twice, off by one day at every date, and a death date a
#     month off. The duplicate row goes, the survivor is corrected from
#     the dossier (second-degree murder; sentenced October 7, 1916;
#     release at month precision because the two rows disagreed on the
#     day), and the deleted row takes with it the claim that Earl
#     Warren pardoned him — the 1961 pardon was Governor Brown's.
#
#     HARVEY O'CONNOR existed with only his 1953 McCarthy contempt
#     indictment. He gains vital dates, a biography covering both
#     episodes, and the February 1919 Seattle criminal-anarchy case
#     that was abandoned without trial.
#
#     Evidentiary rules per the UW chronology's own warning that its
#     dates are newspaper dates: Dezettel's fifty days is not entered
#     as time served, Lavine's projected June 26, 1917 release is not
#     entered as a release, Spear's dismissal date is not a release
#     (his bail record is silent), Johnson's "58 days" is kept as
#     reported against a 55-day calendar interval, and nobody but
#     O'Connor and Billings gets vital dates — the rest are common
#     names, and attaching records on a name match is how Jacob Tori
#     ended up with Jacob Riis's photograph.
#
#     FLAGGED, NOT CHANGED: ed-burns (the Sacramento defendant who died
#     in custody in November 1918) carries an arrest date of February
#     1924 — five years after his own death. He is also a different man
#     from the Ed Burns of the 1920 Globe Building raid. And
#     thomas-mooney has THREE case rows for one imprisonment, the same
#     off-by-one import as Billings; he deserves his own pass.
#
# PLACEMENT TAIL: up to four institutions created (Boise City Jail,
# Calipatria Town Jail, Gamboa Stockade, Everett Jail).
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-47.sh

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
echo "  Batch 47 — the UW IWW list: 12 new records, 2 corrections"
echo "==================================================================="

run "add-uw-iww-arrestees" bash database/data/add-uw-iww-arrestees.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 47 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
