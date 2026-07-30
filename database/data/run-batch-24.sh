#!/usr/bin/env bash
#
# BATCH 24 -- John Letcher.
#
#   add-john-letcher   New record. Virginia{39}s wartime governor, arrested
#                      at Lexington on May 20, 1865 on General Grant{39}s
#                      order as a "particularly obnoxious" political
#                      leader, held from May 24 in Carroll Prison in
#                      Washington, and paroled July 10, 1865 after
#                      fifty-one days without charge or trial. Pardoned
#                      by President Johnson on January 15, 1867.
#
#                      Cross-references his cellmate Zebulon Vance, who
#                      is already in the database and whose entry names
#                      Letcher in return.
#
# PLACEMENT TAIL INCLUDED. This creates a record, so it needs a sort
# order: prisoners:place-zero-sort-by-year drops it into the right slot
# by year instead of leaving it at 0, where it would sit at the top of
# the newest-first list.
#
# prisoner:add refuses duplicate names, so a second run reports that he
# already exists and changes nothing; the placement pass is idempotent.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-24.sh

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
echo "  Batch 24 — John Letcher"
echo "==================================================================="

run "add-john-letcher" bash database/data/add-john-letcher.sh
run "place-zero-sort-by-year" php artisan prisoners:place-zero-sort-by-year --apply

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 24 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
