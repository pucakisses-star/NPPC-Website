#!/usr/bin/env bash
#
# BATCH 29 -- stop publishing 250 years of imprisonment that never happened.
#
# THE BUG. PrisonerCase::computeImprisonedForDays() measured custody with
# Carbon diffInDays(), which is ABSOLUTE. So a case row whose release date
# fell BEFORE its incarceration date did not produce a negative number, or
# a null, or an error. It produced a large, entirely believable figure.
#
#   Paul Magno: arrest 2013-12-31 against release 1986-02-20, published on
#   his public profile as “Imprisoned For 27 years 10 months 10 days”.
#
# 75 case rows are in that state, 74 of them the prisoner’s only case, so
# for those the whole headline “Time Imprisoned” figure was invented:
# 93,409 days, about 256 years of imprisonment that no record actually
# documents.
#
# WHY THE ROWS ARE INVERTED. They are not typos, mostly. They are two
# different episodes in one row. The people at the top of the list --
# Daniel and Philip Berrigan, James Lawson, the Plowshares defendants,
# Chase Iron Eyes -- were arrested many times across a lifetime, and a
# single case row cannot hold two arrests. An import filled the arrest
# from one episode and the release from an earlier, unrelated one. A
# smaller group is inverted by only a day or two, which does look like
# data entry.
#
# THE FIX IS IN THE MODEL, not in the data, because the model is the only
# thing that writes these columns and the recompute command calls the same
# method. Both compute methods now return null when the end precedes the
# start, exactly as the age hook in Prisoner::saving() suppresses an
# impossible age instead of publishing the absolute difference. A
# suppressed counter is honest; a fabricated one is not.
#
# VERIFIED AGAINST THE LIVE SNAPSHOT before shipping: of 8,709 case rows,
# exactly 75 change value, every one of them from a number to null, and
# NONE gains or alters a non-null figure. The guard cannot disturb a
# correct duration.
#
# WHAT THIS BATCH DOES.
#
#   audit-inverted-case-dates    Read-only. Prints the 75 as a worklist,
#                                split into “two different episodes”
#                                (needs research) and “likely a slip”
#                                (fixable by inspection). Run first so the
#                                list is in the log before the counters go
#                                quiet -- once suppressed they are
#                                invisible, and an invisible problem does
#                                not get fixed.
#
#   recompute-imprisonment       Writes the corrected columns. Clears the
#                                75 fabrications and also refreshes any
#                                open-ended counter that had gone stale
#                                since its row was last saved.
#
# THE ROWS THEMSELVES ARE NOT REPAIRED HERE. Deciding which dates belong
# together, and whether a second case row is needed, takes research per
# person. This batch stops the site asserting a false number in the
# meantime.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-29.sh

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
echo "  Batch 29 — inverted case dates and fabricated day counters"
echo "==================================================================="

run "audit-inverted-case-dates" php artisan prisoners:audit-inverted-case-dates
run "recompute-imprisonment" php artisan prisoners:recompute-imprisonment --apply

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 29 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
