#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/run_bop_name_lookup.sh
#
# Runs the new prisoners:lookup-bop-by-name artisan command, which
# walks every prisoner from the 80s-present whose case is missing
# either a BOP register number or a release date, queries the
# public BOP inmate locator by first + last name, and patches in
# the missing fields only when exactly one match comes back.
#
# Skips prisoners with multiple case rows (ambiguous match) and
# prisoners whose name search returns multiple BOP results.
#
# Idempotent: re-running it only acts on records that are still
# missing data.
set -e

php artisan prisoners:lookup-bop-by-name

echo
echo "BOP name-based backfill complete."
