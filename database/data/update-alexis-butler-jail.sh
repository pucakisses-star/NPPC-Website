#!/usr/bin/env bash
#
# Record Alexis Butler's February 2020 jail stint. When the felony
# false-imprisonment / conspiracy charges were added, she was rearrested on
# Friday, February 7, 2020 and released on Monday, February 10, 2020 —
# three days in custody. Her case currently records only the December 3,
# 2019 arrest and the release on bond, with no incarceration/release dates.
#
# Idempotent: only sets the fields while empty.
#   bash database/data/update-alexis-butler-jail.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "alexis-butler")->first();
if (! $p) { echo "alexis-butler not found\n"; return; }
$c = $p->cases()->first();
if (! $c) { echo "no case\n"; return; }

if (empty($c->incarceration_date)) { $c->incarceration_date = "2020-02-07"; echo "SET incarceration_date 2020-02-07\n"; }
if (empty($c->release_date))       { $c->release_date = "2020-02-10"; echo "SET release_date 2020-02-10\n"; }
if (empty($c->imprisoned_for_days)) { $c->imprisoned_for_days = 3; echo "SET imprisoned_for_days 3\n"; }

if ($c->isDirty()) { $c->save(); echo "saved\n"; } else { echo "nothing to do\n"; }
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Alexis Butler February 2020 jail stint recorded."
