#!/usr/bin/env bash
#
# RUNBOOK for the July 27, 2026 batch. Runs every outstanding data script from
# that session in dependency order, so nothing is missed and nothing runs out
# of sequence. Every step is idempotent -- already-applied steps report
# "skipped" or simply rewrite the same values -- so this is safe to re-run.
#
# Order matters in three places:
#   1. Era fill runs BEFORE placement, because auto-placement uses the era as a
#      chronology fallback when a record has no dated case.
#   2. Duplicate removal runs BEFORE placement, so deleted records do not get
#      slotted into the sort order first.
#   3. Placement runs BEFORE any resequence/reorder, because the placement
#      commands identify unplaced records by sort_order = 0 -- a resequence
#      gives every record a real position and turns them into no-ops.
#
# The two bulk ordering operations are opt-in, since they touch many rows:
#   REORDER_1700S=1  reorder the 1700s block newest-first
#   RESEQUENCE=1     renumber every prisoner 1..N by list position
#
#   bash database/data/run-pending-2026-07-27.sh
#   REORDER_1700S=1 RESEQUENCE=1 bash database/data/run-pending-2026-07-27.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

step() { echo; echo "=============================================================="; echo ">>> $*"; echo "=============================================================="; }

# ---- 1. Taxonomy ----------------------------------------------------------
step "Remove Tax Resistance / Anti-tax ideologies"
php artisan prisoners:remove-tax-ideologies

# ---- 2. Data corrections that affect placement ----------------------------
step "Era for the eight 1901 Free Society anarchists"
bash database/data/add-era-1901-anarchists.sh

step "Remove duplicate colonial records"
bash database/data/remove-duplicate-colonial-records.sh

step "Whiskey Rebellion roster: dates for 20, create 24 more"
bash database/data/apply-whiskey-rebellion-roster.sh

# ---- 3. Placement (must precede any resequence) ---------------------------
step "Place zero-sort prisoners via anchor rules"
php artisan prisoners:place-zero-sort

step "Auto-place remaining zero-sort prisoners into affiliation clusters"
php artisan prisoners:auto-place-zero-sort

step "Place the 1901 anarchists after Abraham Isaak"
bash database/data/place-1901-anarchists.sh

# ---- 4. Individual record fixes -------------------------------------------
step "Matthew Lyon: Sedition Act record"
bash database/data/fix-matthew-lyon.sh

step "Samuel Seabury: birth/death dates"
bash database/data/set-samuel-seabury-dates.sh

step "Samuel Seabury: 1775 New Haven detention and bio"
bash database/data/fix-samuel-seabury-case.sh

step "Hokoleskwa and Allanawissica: renames, dates, portrait"
bash database/data/update-hokoleskwa-allanawissica.sh

step "Terry Bisson: photo, vitals, 1985 contempt case"
bash database/data/fix-terry-bisson.sh

step "Bob Lederer: photo, birth year, 1985 contempt dates"
bash database/data/fix-bob-lederer.sh

step "Maria Cueto: birth/death dates and portrait"
bash database/data/fix-maria-cueto.sh

step "Eve Rosahn: UVA Law Archives portrait"
bash database/data/set-eve-rosahn-photo.sh

step "Suffrage prisoners: clear stale custody flags (107-year bug)"
bash database/data/fix-suffrage-custody-flags.sh

# ---- 5. Dashboard ---------------------------------------------------------
step "Dashboard link: KCRG Cedar Rapids arrest story"
bash database/data/add-dashboard-link-cedar-rapids-arrest.sh

# ---- 6. Opt-in bulk ordering ---------------------------------------------
if [ "${REORDER_1700S:-0}" = "1" ]; then
    step "Reorder the 1700s block newest-first (APPLYING)"
    APPLY=1 bash database/data/reorder-1700s-chronologically.sh
else
    echo
    echo "SKIPPED: 1700s reorder. Preview it with:"
    echo "  bash database/data/reorder-1700s-chronologically.sh"
    echo "then re-run this runbook with REORDER_1700S=1 to apply."
fi

if [ "${RESEQUENCE:-0}" = "1" ]; then
    step "Resequence every prisoner 1..N by list position"
    bash database/data/resequence-sort-order.sh
else
    echo
    echo "SKIPPED: global resequence. Add RESEQUENCE=1 to renumber every"
    echo "prisoner 1..N so admin drag-and-drop numbering starts clean."
fi

echo
echo "=============================================================="
echo "Runbook complete."
echo "Spot-check a photo swap with:"
echo "  bash database/data/check-prisoner-photo.sh bob-lederer"
echo "=============================================================="
