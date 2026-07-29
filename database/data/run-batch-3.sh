#!/usr/bin/env bash
#
# BATCH 3 -- undo the bio-age strip, and re-run the charge trimmer with
# its diagnostics.
#
#   revert-bio-age-strip    restores the 694 descriptions the strip
#                           mangled ("At age 17, he was sentenced" had
#                           become "At, he was sentenced") from the
#                           pre-strip snapshot, and clears the derived
#                           birthdates the command wrote -- guarded, so
#                           only a value exactly matching the bad
#                           derivation is cleared. The strip command is
#                           retired and removed from batch 2; those
#                           birth years get filled by researching the
#                           people individually instead.
#
#   trim-charge-context     ran as 0-of-8,370 on the server while
#                           trimming 2,334 on identical values locally.
#                           Newlines were tested and ruled out; the
#                           leading suspect is invalid UTF-8 in the
#                           column, which makes every /u regex no-op
#                           silently. The command now converts
#                           Windows-1252 on the way in, and if it still
#                           changes nothing it prints PHP and PCRE
#                           versions and the actual bytes of the first
#                           three values, so the next log settles the
#                           question either way.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-3.sh

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
echo "  Batch 3 — revert the age strip; retry the charge trim"
echo "==================================================================="

run "revert-bio-age-strip" bash database/data/revert-bio-age-strip.sh
run "trim-charge-context (dry run)" php artisan prisoners:trim-charge-context
run "trim-charge-context (apply)" php artisan prisoners:trim-charge-context --apply

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 3 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
