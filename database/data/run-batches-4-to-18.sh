#!/usr/bin/env bash
#
# RUN BATCHES 4 THROUGH 18 IN ORDER -- one command instead of fifteen.
#
# Batches 1 (run-pending.sh), 2 and 3 are already applied and are NOT
# re-run here. Everything below has accumulated since then:
#
#   4-14, 18   researched birthdates, rounds 1-12. Roughly 90 records
#              get a birth or death date, every one of them found
#              STATED in a source -- court filings, obituaries,
#              Library of Congress authority records, movement
#              archives. Nothing is derived from a reported age.
#   15         Zebulon Vance added: arrested on his 35th birthday, held
#              at the Old Capitol Prison without charge, paroled after
#              54 days. Includes the sort-order placement pass.
#   16         Wesley Somers: birthdate corrected from a court record
#              and a fabricated death date cleared -- the record had
#              him dying twenty-six years before he was born.
#   17         Haniffa bin Osman: his description had him as his own
#              co-defendant, a retired Indonesian Marine Corps general.
#              He was a Singaporean arms broker.
#
# THIS FILE IS NAMED FOR ITS RANGE ON PURPOSE. It is not a rolling
# "pending" list to be appended to forever -- when batch 19 exists it
# gets its own runner, and this one is finished. Every script inside is
# idempotent, so a second run of this file is safe and reports nothing
# to do.
#
# One failing batch does not abort the rest; failures are listed at the
# end.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batches-4-to-18.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"
    shift
    echo
    echo "###################################################################"
    echo "#  ${label}"
    echo "###################################################################"
    if "$@"; then
        return 0
    fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}")
    return 0
}

echo "==================================================================="
echo "  Batches 4-18"
echo "==================================================================="

for n in 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18; do
    run "batch ${n}" bash "database/data/run-batch-${n}.sh"
done

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  All batches 4-18 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed batch(es):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
    echo
    echo "  Each batch is idempotent — re-running a failed one is safe."
fi
echo "==================================================================="
