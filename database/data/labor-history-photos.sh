#!/usr/bin/env bash
#
# Labor-history photo batch (July 2026): Centralia 1919, Matewan, the Sweet
# trials, Gastonia 1929, the 1928 New Bedford strike, and the Greco-Carrillo
# case.
#
#  1. Merges the two Centralia full-name duplicate pairs surfaced by this
#     batch: Bert Bland / James Bertie Bland and O.C. Bland / Oliver Charles
#     Bland (the dup records carry redundant copies of the same Montesano
#     conviction, which are dropped; their death dates backfill).
#  2. Attaches the 17 caption-verified portraits via
#     prisoners:attach-labor-history-photos (fill-if-empty).
#
# Idempotent throughout.
#
# Run from the repo root:  bash database/data/labor-history-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoners:merge-duplicates --only=bert-bland,oc-bland --apply

php artisan prisoners:attach-labor-history-photos

echo
echo "Done. Labor-history merges and portraits applied."
