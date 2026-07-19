#!/usr/bin/env bash
#
# Dashboard newswire additions for the week of July 12-19, 2026: the
# July 18 national day of protest against data centers (142 protests,
# 42 states) and the Good Trouble Lives On weekend of action (400+
# demonstrations, all 50 states, July 17-19). The Fergie Chambers
# arrest in Spain (July 14) was already on the newswire.
#
# Idempotent (updateOrCreate keyed by URL).
#
# Run from the repo root:  bash database/data/add-dashboard-week-2026-07-19.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan dashboard:add-week-2026-07-19

echo
echo "Done. Week of July 19, 2026 dashboard links added."
