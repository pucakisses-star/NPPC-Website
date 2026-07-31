#!/usr/bin/env bash
#
# BATCH 50 -- the Camden 28, corrected from the curator's research report.
#
#   fix-camden-28
#
#     THE OUTCOMES WERE THE BIG ERROR. Every record said "acquitted";
#     the federal court's February 7, 1973 opinion names the SEVENTEEN
#     actually tried, whom the jury acquitted on May 20, 1973. The TEN
#     severed defendants now say severed-and-dismissed, and ANITA RICCI
#     now records her pre-trial guilty plea to a lesser charge instead
#     of an acquittal that never happened.
#
#     Cookie Ridolfi and Kathleen Ridolfi merge into one woman.
#     Margaret Innes becomes Margaret Inness, the court and obituary
#     spelling. Father Michael Doyle stops being jailed for the whole
#     trial (his release read 1973-05-19, the eve of the verdict; the
#     defendants were bailed by about mid-September 1971), his death
#     moves to November 4, 2022, and he regains the Camden 28
#     affiliation the batch 45 split left empty.
#
#     BIRTH YEARS DERIVED FROM AGE AT INDICTMENT, per the curator, at
#     circa precision (may be off by one). Exact dates where the
#     dossier established them; deaths for Inness (August 22, 2021 —
#     fifty years to the day after the raid), McGowan, Swinglish,
#     Williamson, Abdoo and Giocondo.
#
#     Arrests at MONTH precision, August 1971, because the twenty
#     August 22 arrests and the eight around August 27 are not reliably
#     assigned by name; day precision only for McGowan (obituary),
#     Doyle, Ridolfi and Grady. No individual release dates — none is
#     documented; the collective-bail three weeks lives in the texts.
#
#     Flagged, not changed: edward-murphy death 2012-04-04 and
#     sarah-tosi death 2006-04-15, both unresolvable from the dossier.
#
# ONE RECORD IS DELETED (cookie-ridolfi, into kathleen-ridolfi). ONE
# SLUG CHANGES (margaret-innes -> margaret-inness, a demonstrable
# spelling error). No photographs are touched.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-50.sh

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
echo "  Batch 50 — the Camden 28"
echo "==================================================================="

run "fix-camden-28" bash database/data/fix-camden-28.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 50 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
