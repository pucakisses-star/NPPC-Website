#!/usr/bin/env bash
#
# BATCH 46 -- the IWW duplicates.
#
#   merge-duplicate-prisoners-2
#
#     31 merges across 28 curator-identified clusters, nearly all one
#     pattern: a NAMED ROSTER IMPORTED TWICE. One fuller record against
#     one thin row from the NCLB's 1919 "War-Time Prosecutions and Mob
#     Violence" or the ILD class-war-prisoner rosters, the roster row
#     carrying a variant spelling or bare initials.
#
#     JACOB TORI'S PHOTO WAS JACOB RIIS — and Riis's exact vital dates
#     were in the record's date fields, killing the man four years
#     before his own conviction. Cleared; the Leavenworth mugshot from
#     the duplicate replaces the portrait.
#
#     THE TWO BEYER PHOTOS WERE TWO DIFFERENT MEN. The prison mugshot
#     (no. 4914, a man in his fifties — the record says he was 56 at
#     Everett) replaces a Houghton Library cabinet card of a young man
#     that was very likely never him.
#
#     THREE CASE ROWS MOVE instead of dying with their duplicates:
#     Vincent Saint John gains his 1907 Goldfield arrest, J. H. Beyer
#     gains the Everett Massacre detention, Manuel Rey y Garcia gains
#     the only dated custody span of his pair.
#
#     A THIRD HAYWOOD (william-d-haywood, Steunenberg-only) surfaced
#     while applying the named pair; all three collapse into
#     bill-haywood.
#
#     Blackie Ford, on the curator's list, was already merged in batch
#     45 and is skipped as already gone.
#
# NO PLACEMENT TAIL. Nothing is created.
#
# 31 RECORDS ARE DELETED, each after its survivor is written. Every
# conflict between a pair (Stewart's release, Nef's death, Baldazzi's
# Leavenworth span, MacDonald's commutation month) is resolved
# explicitly and preserved in the payload notes, which the run prints.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-46.sh

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
echo "  Batch 46 — the IWW duplicates: 31 merges"
echo "==================================================================="

echo
echo "Records before:"
php artisan tinker --execute='echo "  ", \App\Models\Prisoner::withoutGlobalScopes()->count(), "\n";'

run "merge-duplicate-prisoners-2" bash database/data/merge-duplicate-prisoners-2.sh

echo
echo "Records after:"
php artisan tinker --execute='echo "  ", \App\Models\Prisoner::withoutGlobalScopes()->count(), "\n";'

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 46 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
