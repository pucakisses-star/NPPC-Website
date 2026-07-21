#!/usr/bin/env bash
#
# Correct the custody dates for the two Oberlin-Wellington Rescue defendants
# in the database. Both records only had the year 1859 and their FORMAL
# sentences; in fact both entered the Cuyahoga County Jail on April 15, 1859
# and stayed far longer:
#   - Charles Langston: formal sentence 20 days, but held April 15 - July 6,
#     1859 (~82 days) until the general release.
#   - Simeon Bushnell: formal sentence 60 days from May 11, but held April 15
#     - July 11, 1859 (~87 days); not part of the July 6 release.
#
# Idempotent: only sets the dates while release_date is still empty.
#   bash database/data/update-oberlin-rescuers-custody.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
// Charles Langston
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "charles-langston")->first();
if ($p && ($c = $p->cases()->first()) && empty($c->release_date)) {
    $c->setPartialDate("arrest_date", 1859, 4, 15);
    $c->setPartialDate("incarceration_date", 1859, 4, 15);
    $c->setPartialDate("release_date", 1859, 7, 6);
    $c->imprisoned_for_days = 82;
    $c->sentence = "Formal sentence: twenty days in jail and a fine. In fact he entered the Cuyahoga County Jail on April 15, 1859 and remained in custody until the general release on July 6, 1859 — about 82 days.";
    $c->save();
    echo "UPDATED charles-langston (Apr 15 - Jul 6 1859, ~82 days)\n";
} else {
    echo "charles-langston: skipped (missing or already has release date)\n";
}

// Simeon Bushnell
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "simeon-bushnell")->first();
if ($p && ($c = $p->cases()->first()) && empty($c->release_date)) {
    $c->setPartialDate("arrest_date", 1859, 4, 15);
    $c->setPartialDate("incarceration_date", 1859, 4, 15);
    $c->setPartialDate("release_date", 1859, 7, 11);
    $c->imprisoned_for_days = 87;
    $c->sentence = "Formal sentence: sixty days in jail (from May 11) and a \$600 fine. He entered the Cuyahoga County Jail on April 15, 1859 and, not being part of the July 6 general release, remained until July 11, 1859 — about 87 days.";
    $c->save();
    echo "UPDATED simeon-bushnell (Apr 15 - Jul 11 1859, ~87 days)\n";
} else {
    echo "simeon-bushnell: skipped (missing or already has release date)\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Oberlin-Wellington rescuers custody dates updated."
