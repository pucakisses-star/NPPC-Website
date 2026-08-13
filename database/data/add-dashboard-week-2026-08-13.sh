#!/usr/bin/env bash
#
# Dashboard newswire additions for the week of August 6-13, 2026, which is
# where the wire had run out — its most recent entry was August 6.
#
# Six rows, all with map pins:
#
#   Keith LaMar's three-year execution reprieve, and him speaking from
#   Ohio death row. The one that matters most to this archive: a
#   Lucasville defendant, and he was not on the wire at all.
#
#   The AP count of more than fifty parents and spouses of active-duty
#   service members detained since January 2025.
#
#   Rallies across Texas at one month since the ICE killing of Lorenzo
#   Salgado Araujo — pinned at Houston like the July rows, so the month
#   of protest reads as one place rather than scattering.
#
#   The prosecution of journalist Georgia Fort for filming a protest at a
#   St. Paul church. Older than this week and dated to the January arrest
#   rather than to today, because it belongs in the wire where it
#   happened; it had simply been missed.
#
#   The State Department visa revocations, and the ICE electric-shock
#   glove purchase.
#
# Every URL was requested before it was written. The Minnesota Reformer
# report on Fort's motion to dismiss was the natural source for that row
# and is deliberately absent: it answers 403 to every automated request,
# so it could not be confirmed reachable, and the U.S. Press Freedom
# Tracker record of the same prosecution stands in its place.
#
# Idempotent (updateOrCreate keyed by URL).
#
# Run from the repo root:  bash database/data/add-dashboard-week-2026-08-13.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan dashboard:add-week-2026-08-13

echo
echo "Done. Week of August 13, 2026 dashboard links added."
