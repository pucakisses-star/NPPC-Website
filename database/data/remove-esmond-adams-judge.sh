#!/usr/bin/env bash
#
# Remove the erroneous "Esmond Adams" prisoner record.
#
# This is a data error: "Esmond Adams" is Judge Kimberly Esmond Adams, who
# PRESIDES over the Fulton County "Stop Cop City" RICO case — not a defendant.
# Her name was scraped into the defendant list by mistake (the record's
# "case was severed" line is not a real defendant fact). Confirmed against
# news coverage of the case.
#
# Deletes the prisoner and its case(s). Guarded so it only fires on the exact
# erroneous record (slug esmond-adams, Georgia) and never a real prisoner.
# Idempotent: if already removed, it does nothing. Run from the repo root:
#   bash database/data/remove-esmond-adams-judge.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "esmond-adams")->first();
if (! $p) { echo "esmond-adams: not found (already removed).\n"; return; }

// Safety guard: only delete if this is the known judge-error record.
if ($p->name !== "Esmond Adams" || strtolower((string) $p->state) !== "georgia") {
    echo "esmond-adams: record does not match the expected data-error signature; NOT deleting.\n";
    return;
}

$cases = \App\Models\PrisonerCase::where("prisoner_id", $p->id)->count();
\App\Models\PrisonerCase::where("prisoner_id", $p->id)->delete();
$p->delete();
echo "Removed prisoner Esmond Adams (the presiding judge, not a defendant) and {$cases} case(s).\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Erroneous Esmond Adams (judge) record removed."
