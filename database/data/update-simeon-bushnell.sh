#!/usr/bin/env bash
#
# Set Simeon Bushnell's middle name and dates:
#   middle name : Martin
#   born        : 5 December 1829 (Rome, Oneida County, New York)
#   died        : 8 December 1861 (aged 32)
#
# Idempotent. Run from the repo root:
#   bash database/data/update-simeon-bushnell.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "simeon-bushnell")->first();
if (! $p) { echo "simeon-bushnell not found.\n"; return; }
if (empty($p->middle_name)) { $p->middle_name = "Martin"; }
$p->setPartialDate("birthdate", 1829, 12, 5);
$p->setPartialDate("death_date", 1861, 12, 8);
$p->save();
echo "Set Simeon Martin Bushnell: born 1829-12-05, died 1861-12-08.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Simeon Bushnell updated."
