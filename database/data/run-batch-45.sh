#!/usr/bin/env bash
#
# BATCH 45 -- the duplicate audit.
#
#   merge-duplicate-prisoners-1
#
#     NINETEEN DUPLICATE PAIRS MERGED and one record split, out of 54
#     candidates read by hand.
#
#     ELEVEN OF THE NINETEEN are the September 2019 Houston Ship Channel
#     action, imported twice — once as the "Greenpeace 28" with the Texas
#     state charge, once as the "22 federally charged" with photographs.
#     Eleven of the twelve in the first import are in the second, matching
#     on reported age AND state. Mariah De Los Santos is the twelfth and
#     has no counterpart; she is left alone.
#
#     MICHAEL DOYLE IS NOT A DUPLICATE, HE IS TWO MEN IN ONE ROW: Father
#     Michael Doyle of the Camden 28, who died in 2022 and already has his
#     own correct record, and Michael Doyle the Molly Maguire, hanged at
#     Carbon County Prison on June 21, 1877. The row carried both
#     affiliations, both cases, and a pair of vital dates belonging to
#     neither man. It is stripped back to the Molly Maguire. Nothing is
#     deleted — the priest already exists in full.
#
#     TWO PAIRS WERE ONLY FINDABLE BY NAME ORDER: Kim Irene / Irene Kim,
#     and Harrison George / George Harrison. In the second, the CORRECTLY
#     named record is the thin one, so the biography is carried onto it
#     rather than the other way round.
#
# THE FULL FINDINGS, including everything NOT merged and why, are in
# database/data/DUPLICATES-REPORT.md. Read it: it lists fathers and sons
# the audit refused to merge (Abraham Isaak and his son, Billy Frank Sr.
# and Jr., Fred Shuttlesworth and his son), pairs the records themselves
# disambiguate, and a dozen probable duplicates left for a curator —
# including Lucy G. Branham and Lucy Gwynne Branham, who may well be a
# mother and a daughter who were BOTH arrested.
#
# The audit is reproducible and read-only:
#   php artisan prisoners:audit-duplicate-names --show-ruled-out
#
# NO PLACEMENT TAIL. Nothing is created.
#
# NINETEEN RECORDS ARE DELETED. Each survivor is written first, and the
# script refuses to delete any row holding the only photograph of its pair.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-45.sh

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
echo "  Batch 45 — 19 duplicate pairs merged, 1 record split"
echo "==================================================================="

echo
echo "Records before:"
php artisan tinker --execute='echo "  ", \App\Models\Prisoner::withoutGlobalScopes()->count(), "\n";'

run "merge-duplicate-prisoners-1" bash database/data/merge-duplicate-prisoners-1.sh

echo
echo "Records after:"
php artisan tinker --execute='echo "  ", \App\Models\Prisoner::withoutGlobalScopes()->count(), "\n";'

echo
echo "Re-running the audit to show what is left:"
php artisan prisoners:audit-duplicate-names --min-score=8

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 45 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
