#!/usr/bin/env bash
#
# BATCH 32 -- twelve more Silent Sentinels, and two more portraits.
#
#   fix-suffragist-identities-2      Dossier entries 17 to 28. Lifespans
#                                    and documented custody for twelve
#                                    records, one name correction
#                                    (elizabeth-hoff -> Elizabeth Minnie
#                                    Huff, WHICH CHANGES THE SLUG), and
#                                    Elizabeth McShane gains all three of
#                                    her terms, including the November
#                                    1917 one in which she was force-fed.
#
#   attach-silent-sentinel-photos-2  Ernestine Hara from the Library of
#                                    Congress and Josephine Bennett from
#                                    the Harriet Beecher Stowe Center,
#                                    the latter photographed in
#                                    Washington in the very month she was
#                                    jailed. Ellen Winsor is listed and
#                                    will report missing until her
#                                    Historical Society of Pennsylvania
#                                    image can be fetched by hand.
#
# MOST OF THESE TERMS END WITHOUT A RELEASE DATE ON PURPOSE. Nine have a
# documented sentencing date and no documented discharge; storing the
# arithmetic end of the sentence would present a calculation as a record.
# Only the five genuinely documented discharges are entered.
#
# NO PLACEMENT TAIL. Nothing is created; the new rows are case rows on
# people who already exist.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-32.sh

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
echo "  Batch 32 — Silent Sentinels, dossier entries 17-28"
echo "==================================================================="

run "fix-suffragist-identities-2" bash database/data/fix-suffragist-identities-2.sh
run "attach-silent-sentinel-photos-2" bash database/data/attach-silent-sentinel-photos-2.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 32 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
