#!/usr/bin/env bash
#
# Correct John Berry's (blacklisted film/theater director) birthdate.
#
# His record stored September 4, 1917, but every reliable source gives
# September 6, 1917 (born Jak Szold in New York City) -- Wikipedia, his
# 1999 obituary, and Prabook all agree. The death date (November 29, 1999)
# is already correct and is left unchanged.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-john-berry-birthdate.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "john-berry")->first();
if (! $p) { echo "john-berry not found.\n"; return; }

$p->setPartialDate("birthdate", 1917, 9, 6);
$p->save();

$p->refresh();
echo "Updated john-berry: birthdate now {$p->birthdate} (was 1917-09-04).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. John Berry birthdate corrected to 1917-09-06."
