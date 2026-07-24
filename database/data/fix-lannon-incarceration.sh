#!/usr/bin/env bash
#
# Fix Albert Lannon's imprisonment counter. His case had the incarceration date
# set to the ARREST date (June 19, 1951), so the site computed "imprisoned for
# 5 years 8 months 9 days" (1951 arrest to 1957 release). But like the other
# New York second-string Smith Act defendants, he was free on bail through the
# appeals and only began serving on January 11, 1955, until his release on
# February 28, 1957 — about 2 years 1.5 months.
#
# This sets the incarceration date to the actual serving-start date (arrest date
# is left as-is), so the counter reflects real time served. Idempotent. Run from
# the repo root:
#   bash database/data/fix-lannon-incarceration.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "albert-lannon")->first();
if (! $p) { echo "albert-lannon not found.\n"; return; }

// Fix the case that carries the 1951 arrest/incarceration + 1957 release.
$c = $p->cases()
    ->whereNotNull("release_date")
    ->whereNotNull("incarceration_date")
    ->first() ?: $p->cases()->first();
if (! $c) { echo "No case found.\n"; return; }

$c->setPartialDate("incarceration_date", 1955, 1, 11);   // actual serving start
$c->save();                                              // hook recomputes imprisoned_for_days

$p->refresh();
echo "Updated albert-lannon: incarceration 1955-01-11, release {$c->release_date}, days {$c->imprisoned_for_days}.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Albert Lannon imprisonment counter fixed."
