#!/usr/bin/env bash
#
# BATCH 44 -- Charles H. Moyer and George Arthur Pettibone.
#
#   merge-moyer-pettibone
#
#     Two men were in the database twice. Each pair is one thin record
#     with no photo and one fuller record with a photo:
#
#       charles-h-moyer    -> merged into charles-moyer,    deleted
#       george-a-pettibone -> merged into george-pettibone, deleted
#
#     The record with the PHOTO survives, which also keeps the live
#     /prisoner/ URL — prisoner slugs have no redirect map, so a renamed
#     one just 404s. The initials go in as middle_name ("H." and
#     "Arthur"), which does not touch the slug.
#
#     MOYER GAINS THE 1904 COLORADO IMPRISONMENT, which only the deleted
#     duplicate had, so a man jailed twice was showing one term. His
#     arrest moves off March 30 and onto MARCH 26 at Ouray, on the flag
#     charge; March 30 is where the military imprisonment litigated in
#     Moyer v. Peabody begins, not where he was arrested.
#
#     His Idaho release moves from March 1908 to JANUARY 4, 1908 — the
#     afternoon Pettibone was acquitted and prosecutors asked the court
#     to dismiss. 752 days becomes 686.
#
#     His birthdate drops from 1866-07-04 to year precision. No reliable
#     month and day is established.
#
#     NO PHOTOGRAPH IS TOUCHED ON EITHER OF THOSE FOUR, on instruction.
#
#     Carried in the same run, unrelated: charles-crowley LOSES HIS
#     PHOTO. It is a barely legible newspaper halftone captioned
#     "Charlie Crowley." and credited Wide World Photos, with nothing
#     tying it to the IWW member from Portola. The file stays on disk;
#     only the column is nulled. charles-c-crowley is a DIFFERENT MAN —
#     the Hindu-German Conspiracy detective — and keeps his own.
#
# NO PLACEMENT TAIL beyond one institution: Telluride City Jail is
# created if it does not exist, for the 1904 case.
#
# TWO RECORDS ARE DELETED. Both are checked for a photo first and the
# script refuses to delete either if one has appeared. Case rows cascade,
# so each duplicate takes its own rows with it — which is why the 1904
# case is written onto the survivor before anything is removed.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-44.sh

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
echo "  Batch 44 — Moyer and Pettibone: two duplicate pairs merged"
echo "==================================================================="

run "merge-moyer-pettibone" bash database/data/merge-moyer-pettibone.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 44 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
