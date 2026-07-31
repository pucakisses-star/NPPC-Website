#!/usr/bin/env bash
#
# BATCH 36 -- twenty-one curator corrections, mostly misclassified
# prosecutions.
#
#   fix-state-syndicalism-1   Eleven records carried the boilerplate
#                             federal Espionage/Sedition charge for what
#                             were STATE criminal syndicalism cases in
#                             Washington, California, Idaho and
#                             Pennsylvania -- the wrong sovereign under
#                             the wrong statute. Four renames, two death
#                             claims corrected or withdrawn, one claim of
#                             time served withdrawn, one death sentence
#                             explicitly not read as an execution.
#
#                             FOUR SLUGS CHANGE: Carl Swenson, Pedro
#                             González, Tomás Sarabia Labrada, Charles
#                             Thomas Smit.
#
#                             ONE INSTITUTION IS CORRECTED IN PLACE and
#                             that reaches 151 case rows: San Quentin
#                             State Prison had its city recorded as
#                             Tamal, the site's postal name.
#
# A RECOMPUTE IS INCLUDED because several case rows gain custody dates
# and two gain a death in custody, which the hook mirrors onto the
# release date.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-36.sh

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
echo "  Batch 36 — state syndicalism and misclassified prosecutions"
echo "==================================================================="

run "fix-state-syndicalism-1" bash database/data/fix-state-syndicalism-1.sh
run "recompute-imprisonment" php artisan prisoners:recompute-imprisonment --apply

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 36 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
