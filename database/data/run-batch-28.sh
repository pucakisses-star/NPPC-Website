#!/usr/bin/env bash
#
# BATCH 28 -- two records that describe the wrong events.
#
#   split-cerna-camacho-martinez   One row held two people. It is named
#                                  for Christian Cerna-Camacho (28, Boyle
#                                  Heights, arrested June 11, 2025) and
#                                  charged with his offence, but every
#                                  detail in its description belongs to
#                                  Adrian Andrew Martinez (20, Pico
#                                  Rivera, arrested June 17). The
#                                  description is replaced with
#                                  Cerna-Camacho’s actual case, the
#                                  misspelled surname is corrected from
#                                  Cerno to Cerna, and Martinez is
#                                  created as his own record.
#
#                                  THE SLUG CHANGES with the name fix:
#                                  /prisoner/christian-damian-cerno-camacho
#                                  becomes .../christian-damian-cerna-camacho.
#
#   fix-alkhader-dates             The case row dates Mufid Alkhader’s
#                                  arrest to 2025-02-03, which was in
#                                  fact the eve of his guilty plea. He
#                                  was arrested on 2023-12-07, the day of
#                                  the Temple Israel shooting, and
#                                  remanded without bail the next day, so
#                                  he has been in custody throughout.
#                                  The record understates his
#                                  imprisonment by fourteen months: about
#                                  966 days, not 542. Two dated official
#                                  ages also pin his birth year to 1995.
#
# A PLACEMENT STEP IS INCLUDED because Martinez is a new record and lands
# with sort_order 0.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-28.sh

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
echo "  Batch 28 — conflated and misdated records"
echo "==================================================================="

run "split-cerna-camacho-martinez" bash database/data/split-cerna-camacho-martinez.sh
run "fix-alkhader-dates" bash database/data/fix-alkhader-dates.sh
run "place-zero-sort-by-year" php artisan prisoners:place-zero-sort-by-year --apply

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 28 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
