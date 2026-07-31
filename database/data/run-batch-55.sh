#!/usr/bin/env bash
#
# BATCH 55 -- Diego Vargas released, and the Aurora ATM co-defendants.
#
#   fix-vargas-aurora-three
#
#     DIEGO VARGAS is no longer incarcerated: a BOP-derived index puts
#     his release at February 4, 2025 (register 55070-424), high
#     confidence pending direct BOP confirmation — the hedge is stored
#     in the sentence text. His stub record ("federal arson", a January
#     10 release date contradicting an in-custody flag) becomes the
#     full chronology: first arrest July 1, 2020 on the ATM complaint
#     (span unknown, second case row), definitive continuous detention
#     October 7, 2020 to February 4, 2025 (1,581 days), MCC Chicago,
#     guilty plea June 10, 2021, sixty-month mandatory minimum from
#     Judge Bucklo on March 3, 2022, Allenwood then Schuylkill. The
#     conviction is ONE explosive-device count for the Egg Harbor Cafe
#     explosion; the ATM was relevant conduct, not a conviction.
#
#     FERMIN OCAMPO-TELLEZ and MICHAEL GOMEZ (slug michael-gomez-aurora)
#     are added with what the record supports and no more: arrested
#     July 1, 2020, initial appearances in Chicago, the five-year-max
#     conspiracy charge before Judge Kennelly — and DISPOSITIONS
#     UNRESOLVED. The accessible docket shows continuances, a sealed
#     Gomez status report, an appeal, and a writ to produce
#     Ocampo-Tellez from other custody, but no sentencing record,
#     custody span or release for either man. No spans are invented, no
#     races inferred from surnames, ages stated as reported and not
#     stored as birthdates.
#
#     PHOTOS: all three from the DOJ-published complaint, each labeled
#     by the document itself (paragraphs 17, 18 and 19).
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-55.sh

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
echo "  Batch 55 — Vargas released; the Aurora three complete"
echo "==================================================================="

run "fix-vargas-aurora-three" bash database/data/fix-vargas-aurora-three.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 55 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
