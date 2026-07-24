#!/usr/bin/env bash
#
# Set Sam T. Crane's incarceration and release dates. He was arrested (and jailed)
# on November 5, 1909 in the Spokane IWW free-speech fight and released on
# March 4, 1910. The record already carried arrest_date/release_date but no
# incarceration_date; jailing was immediate in the free-speech fights, so
# incarceration is dated to the arrest day.
#
# Idempotent. Run from the repo root:
#   bash database/data/set-sam-crane-custody-dates.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()
    ->where("slug", "sam-t-crane")
    ->orWhereRaw("LOWER(name) = ?", ["sam t. crane"])
    ->first();

if (! $p) { echo "Sam T. Crane not found.\n"; return; }

$p->in_custody = false;
$p->released = true;
$p->save();

$c = $p->cases()->first();
if (! $c) { $c = new \App\Models\PrisonerCase(); $c->prisoner_id = $p->id; $c->charges = "Street-speaking (free-speech fight)"; }

$c->setPartialDate("arrest_date", 1909, 11, 5);
$c->setPartialDate("incarceration_date", 1909, 11, 5);
$c->setPartialDate("release_date", 1910, 3, 4);
$c->save();

echo "{$p->name}: incarcerated ".($c->partialDateIso("incarceration_date") ?? "-")
    ." -> released ".($c->partialDateIso("release_date") ?? "-")
    ." ({$c->imprisoned_for_days} days)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Sam T. Crane custody dates set (Nov 5, 1909 - Mar 4, 1910)."
