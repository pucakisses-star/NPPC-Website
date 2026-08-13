#!/usr/bin/env bash
#
# Three protest rows for the data-center fight — the movement generating
# the most protest coverage right now, and one the newswire had covered
# only from the top down: the July 18 national day of action (142
# protests, 42 states) and the legislative scramble around it.
#
# What was missing is the local organising underneath that, which is
# where these fights are actually decided:
#
#   Atlanta, Aug 5 — a dozen residents speaking against Digital Realty's
#   $500m West End proposal at the Aug 3 City Council meeting.
#
#   Prince George's County, Maryland, Aug 6 — how activists there paused
#   an AI data center project.
#
#   The national picture, Jul 22 — billions in projects delayed or
#   cancelled by local opposition.
#
# Every URL was requested and read before being written, which mattered
# here: search results attributed a 105-87 NPU-V vote in Atlanta to
# August, and the article itself puts that vote in April. The August
# story is the City Council meeting. The row says so.
#
# Idempotent (updateOrCreate keyed by URL).
#
# Run from the repo root:  bash database/data/add-dashboard-data-center-protests.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan dashboard:add-data-center-protests

echo
echo "Done. Data-center protest links added."
