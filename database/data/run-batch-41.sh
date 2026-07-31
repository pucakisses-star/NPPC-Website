#!/usr/bin/env bash
#
# BATCH 41 -- Erica Meltzer author page and the "Is Colorado in America?"
# article.
#
#   add-erica-meltzer-article   Creates the author record with her bio and
#                               headshot, and publishes an article on the
#                               1904 Western Federation of Miners poster
#                               that got Charles Moyer arrested for flag
#                               desecration.
#
#                               THE BODY IS OUR OWN PROSE, not the
#                               Denverite text, which is Copyright
#                               Colorado Public Radio. Meltzer is credited
#                               at the top and the bottom with a link to
#                               the original. The poster-s thirteen
#                               stripes and Emma Langdon-s 1904 passage
#                               are quoted in full because those are
#                               public domain primary sources.
#
#                               The hero image is the poster itself,
#                               public domain, uncropped.
#
# NO RECOMPUTE. Nothing in this batch touches prisoners or case dates.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-41.sh

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
echo "  Batch 41 — Erica Meltzer author page and article"
echo "==================================================================="

run "add-erica-meltzer-article" bash database/data/add-erica-meltzer-article.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 41 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
