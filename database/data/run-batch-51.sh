#!/usr/bin/env bash
#
# BATCH 51 -- the Camden 28 portraits.
#
#   attach-camden-28-photos
#
#     SIX ATTACHED, each identification anchored to a caption, an
#     obituary, or a face-match against an already-confirmed image --
#     never a name match alone:
#
#       father-michael-doyle   diocesan obituary portrait
#       edward-mcgowan         obituary photograph, with his fiddle
#       john-swinglish         memorial photograph; the handcuffed man
#                              flashing the peace sign on the film
#                              poster is his arrest photo
#       keith-forsyth          WHYY at the Media FBI office, named in
#                              the caption, Bonnie Raines cropped out
#       anne-dunham            2017 SDPB studio photo, identified
#                              against Pommersheim's faculty portrait
#       kathleen-ridolfi       NCIP founders photo, identified against
#                              her confirmed SCU headshot
#
#     FIVE PURSUED AND NOT OBTAINED, with reasons in
#     database/data/photos/CREDITS-camden-28.md: Williamson (obituary
#     Cloudflare-blocked, not in Wayback), Inness (her obituary has NO
#     portrait, stock bird art only), Giocondo / Good / Joan Reilly /
#     Dixon (every online copy of the documentary photos is a 60px or
#     111px thumbnail), Abdoo (undigitized archive), Rosemary Reilly
#     (the FBI surveillance photo is not a portrait).
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-51.sh

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
echo "  Batch 51 — Camden 28 portraits: six attached"
echo "==================================================================="

run "attach-camden-28-photos" bash database/data/attach-camden-28-photos.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 51 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
