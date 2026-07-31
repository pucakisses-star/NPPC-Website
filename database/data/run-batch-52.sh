#!/usr/bin/env bash
#
# BATCH 52 -- the CNVA Polaris Action and Peace Walk nine.
#
#   fix-cnva-polaris
#
#     ALLAN HOFFMAN'S custody is derived as instructed: released May 26,
#     1961 after eighteen days served, so incarceration began May 8 —
#     eighteen days counted back from the documented release. The
#     sixty-day sentence is NOT recorded as served; the counter reads 18.
#
#     BILL HENRY gains his boarding date (November 22, 1960), his
#     one-year sentence and Danbury. DONALD MARTIN gains the same
#     arrest, his bail refusal, and the indefinite youth-offender
#     sentence (up to four years), release unresolved. ED GUERARD stops
#     being "sentenced" — he has no documented prison term — and gains
#     the minor_case flag. SANDERS AND KEYES get their colleges
#     un-swapped (Sanders NYU, Keyes Harvard) and their real jailings
#     from the August 1961 Ethan Allen commissioning protest (77 days
#     sentenced; 17 days reported served, respectively). JERRY WHEELER
#     gains Davis-Monthan and the Pima County Jail, six months
#     documented but not recorded as served. MARJORIE SWANN, already
#     correct to the day, gains Alderson. RICHARD ZINK keeps no custody
#     dates — the 30-days-of-six-months order is definite, the register
#     is not found.
#
#     SEVEN PORTRAITS attached from David Rich's Walk for Peace archive
#     and Gene Keyes's own captioned CNVA archive — including the
#     Monterey caption that contemporaneously corroborates Wheeler's
#     sentence. Two pursued and not obtained (Martin: the 1960 AP photo
#     has no open scan; Zink: obituary Cloudflare-blocked), both listed
#     for drop-in completion. See CREDITS-cnva.md.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-52.sh

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
echo "  Batch 52 — CNVA Polaris Action: nine corrected, seven portraits"
echo "==================================================================="

run "fix-cnva-polaris" bash database/data/fix-cnva-polaris.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 52 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
