#!/usr/bin/env bash
#
# BATCH 42 -- the minor-case sweep, two Silent Sentinel portraits, and
# Samuel Green's biography.
#
#   flag-minor-case-sweep-1
#
#     Flags 57 of the 66 records whose entire documented custody comes to
#     ten days or less. NINE ARE HELD BACK, every one of them because
#     the ten-day figure is not real -- Lucy Burns is stored as jailed
#     two months before she was arrested, Eleanor Calnan has one of her
#     three terms recorded, Gabriel Meyers is dated four years off. The
#     script prints all nine with reasons at the end of the run.
#
#     The flag is an ADMIN FILTER with no public effect, and the curator
#     has already been using it to mean "this custody was short" rather
#     than "this case did not matter" -- Doris Stevens, Fannie Lou Hamer
#     and the lynched Wesley Everest all carry it. This sweep follows
#     that usage.
#
#   attach-silent-sentinel-photos-3
#
#     Amy Juengling and Belle Sheinberg, both cut from the Library of
#     Congress plate of the picket line of November 10, 1917 -- the day
#     they were arrested. The other five identified in batch 34 are not
#     attached and the run says so for each.
#
#     THE loc.gov BLOCK IS GONE. Batches 30 to 32 routed around a
#     Cloudflare challenge by taking Wikimedia mirrors. loc.gov and
#     tile.loc.gov now answer normally, master TIFFs included. hsp.org
#     and siarchives.si.edu still do not.
#
#   fix-samuel-green-bio
#
#     Replaces the biography with the curator supplied text, verbatim.
#     Nothing else on his record changes, and the new text agrees with
#     every date batch 40 left in place.
#
# NO PLACEMENT TAIL. Nothing is created: no prisoners, no cases, no
# institutions. One boolean, two photo paths and one description.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-42.sh

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
echo "  Batch 42 — minor-case sweep, two portraits, one biography"
echo "==================================================================="

run "flag-minor-case-sweep-1"          bash database/data/flag-minor-case-sweep-1.sh
run "attach-silent-sentinel-photos-3"  bash database/data/attach-silent-sentinel-photos-3.sh
run "fix-samuel-green-bio"             bash database/data/fix-samuel-green-bio.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 42 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
