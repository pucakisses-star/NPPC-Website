#!/usr/bin/env bash
#
# Set Dmitriy Guzner's incarceration date to a month-level estimate of
# May 2010. His exact report-to-prison day is not in any primary record
# (BOP's public locator only exposes release dates), but two independent
# lines point to mid-May 2010: the Quinnipiac Chronicle reported he would
# begin his sentence right after the spring 2010 semester, and his BOP
# release (2011-05-13) minus the 366-day term lands in the same window.
#
# Stored at MONTH precision so it displays as "May 2010", signalling that
# the exact day is not known rather than implying a precise date.
#
# Idempotent: only sets it while the field is still empty.
#   bash database/data/set-guzner-incarceration-estimate.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "dmitriy-guzner")->first();
if (! $p) { echo "dmitriy-guzner not found\n"; return; }
$c = $p->cases()->first();
if (! $c) { echo "no case\n"; return; }

if (empty($c->incarceration_date)) {
    $c->setPartialDate("incarceration_date", 2010, 5); // May 2010, month precision
    $c->save();
    echo "SET incarceration_date to May 2010 (month precision)\n";
} else {
    echo "incarceration_date already set (" . $c->incarceration_date . ") — nothing to do\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Guzner incarceration estimate set."
