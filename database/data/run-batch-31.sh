#!/usr/bin/env bash
#
# BATCH 31 -- portraits for six Silent Sentinels.
#
#   attach-silent-sentinel-photos   Julia Emory and Berthe Arnold from
#                                   the Library of Congress National
#                                   Woman’s Party records, Mary Agnes
#                                   Chase from the Smithsonian, Bertha
#                                   Moller from Hennepin County Library
#                                   via DPLA, Anna Herkner from the
#                                   Maryland Women’s Heritage Center, and
#                                   Catherine Boyle from the University
#                                   of Delaware. All public domain or no
#                                   known copyright restrictions;
#                                   provenance in
#                                   database/data/photos/CREDITS-silent-sentinels.md
#
# RUN BATCH 30 FIRST. Anna Herkner is renamed there from "Anne Herkimer"
# and her slug changes with the name. The script tries both slugs, so it
# will not fail if the order slips, but running 30 first is what puts the
# file on the right slug.
#
# TWO ARE MISSING ON PURPOSE and reported rather than hidden: Dr. Sarah
# Hunt Lockrey (hsp.org refuses this environment) and Anna Ginsberg
# Hayutin (a family group in a finding aid, not a served portrait). Drop
# the files into database/data/photos/ and re-run to attach them.
#
# The Magee group photograph is not included: batch 30 declined to settle
# that identity and a portrait would assert it.
#
# NO PLACEMENT OR RECOMPUTE TAIL. Only the photo field changes.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-31.sh

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
echo "  Batch 31 — Silent Sentinel portraits"
echo "==================================================================="

run "attach-silent-sentinel-photos" bash database/data/attach-silent-sentinel-photos.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 31 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
