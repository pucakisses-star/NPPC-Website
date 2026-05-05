#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/resort_prisoners.sh
#
# Reassigns sort_order on every prisoner (10, 20, 30, ...) so the public
# list is bunched chronologically by era, then by earliest case year within
# era, then by name. Run after batch adds so new entries are interleaved
# with existing ones instead of stacking at sort_order = 0.
set -e

php artisan prisoners:resort-by-era

echo
echo "Resort complete."
