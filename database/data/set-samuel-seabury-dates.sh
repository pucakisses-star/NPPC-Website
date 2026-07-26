#!/usr/bin/env bash
#
# Samuel Seabury: birth November 30, 1729; death February 25, 1796.
# Age auto-computes from birth + death.
#
# Idempotent. Run from the repo root:
#   bash database/data/set-samuel-seabury-dates.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "samuel-seabury")->first()
    ?? Prisoner::withoutGlobalScopes()->where("name", "like", "%Samuel Seabury%")->first();
if (! $p) { echo "NOT FOUND: Samuel Seabury\n"; exit(1); }

$p->setPartialDate("birthdate", 1729, 11, 30);
$p->setPartialDate("death_date", 1796, 2, 25);
$p->save();

echo "{$p->name}: born 1729-11-30, died 1796-02-25, age {$p->age}.\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
