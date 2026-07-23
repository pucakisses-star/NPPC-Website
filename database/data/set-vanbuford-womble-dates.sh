#!/usr/bin/env bash
#
# Set VanBuford Womble's birth and death dates:
#   born  1 October 1892
#   died  9 February 1972
#
# Idempotent. Run from the repo root:
#   bash database/data/set-vanbuford-womble-dates.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "vanbuford-womble")->first();
if (! $p) { echo "vanbuford-womble not found.\n"; return; }
$p->setPartialDate("birthdate", 1892, 10, 1);
$p->setPartialDate("death_date", 1972, 2, 9);
$p->save();
echo "Set VanBuford Womble: born 1892-10-01, died 1972-02-09.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. VanBuford Womble dates set."
