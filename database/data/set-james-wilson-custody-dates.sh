#!/usr/bin/env bash
#
# Set James P. Wilson's custody dates (Spokane IWW free-speech fight):
# arrested and jailed November 2, 1909; released March 4, 1910. Fills the
# missing incarceration_date and corrects the arrest date (seed had Nov 1).
#
# Idempotent. Run from the repo root:
#   bash database/data/set-james-wilson-custody-dates.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()
    ->where("slug", "james-p-wilson")
    ->orWhereRaw("LOWER(name) = ?", ["james p. wilson"])
    ->first();

if (! $p) { echo "James P. Wilson not found.\n"; return; }

$p->in_custody = false;
$p->released = true;
$p->save();

$c = $p->cases()->first();
if (! $c) { $c = new \App\Models\PrisonerCase(); $c->prisoner_id = $p->id; $c->charges = "Street-speaking (free-speech fight)"; }
$c->setPartialDate("arrest_date", 1909, 11, 2);
$c->setPartialDate("incarceration_date", 1909, 11, 2);
$c->setPartialDate("release_date", 1910, 3, 4);
$c->save();

echo "{$p->name}: incarcerated ".$c->partialDateIso("incarceration_date")." -> released ".$c->partialDateIso("release_date")." ({$c->imprisoned_for_days} days)\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. James P. Wilson custody dates set (Nov 2, 1909 - Mar 4, 1910)."
