#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/fetch_cc_photos.sh
#
# Fetches missing prisoner photos from Wikimedia Commons (Creative Commons
# / public domain) and saves attribution + license alongside.
#
# Workflow:
#   1. Apply the new migration that adds photo_source_url, photo_attribution,
#      and photo_license columns.
#   2. Run the prisoners:fetch-cc-photos command.
#
# Useful options for the artisan command:
#   --dry-run           Preview without writing
#   --limit=20          Cap number of prisoners processed
#   --overwrite         Re-fetch even if photo exists
#   --names="X, Y, Z"   Only run for specific names
set -e

php artisan migrate --path=database/migrations/2026_05_05_000001_add_photo_attribution_to_prisoners.php

php artisan prisoners:fetch-cc-photos

echo
echo "CC photo fetch complete."
