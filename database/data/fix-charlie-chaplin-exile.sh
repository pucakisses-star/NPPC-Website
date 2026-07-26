#!/usr/bin/env bash
#
# Charlie Chaplin was EXILED, not imprisoned. His U.S. re-entry permit was
# revoked on September 19, 1952 and he was barred from returning to the United
# States until April 1972 -- about 19.5 years. The record stored that span as a
# ~19.5-year incarceration ("IMPRISONED FOR 19 YEARS..."). This reclassifies it
# as exile (in_exile_since 1952 -> end_of_exile 1972) and clears the bogus
# incarceration dates.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-charlie-chaplin-exile.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()
    ->where("slug", "charlie-chaplin")
    ->orWhereRaw("LOWER(name) IN (?,?)", ["charlie chaplin", "charles chaplin"])
    ->first();
if (! $p) { echo "Charlie Chaplin not found.\n"; return; }

// Exile, not custody. He returned in 1972 and died in Switzerland in 1977, so
// he is not CURRENTLY in exile.
$p->in_custody = false;
$p->currently_in_exile = false;
$p->in_exile = true;
$p->released = true;
$p->save();

$c = $p->cases()->first();
if ($c) {
    $c->incarceration_date = null;   // clear the bogus ~19.5-year incarceration
    $c->release_date = null;
    $c->arrest_date = null;          // never arrested
    $c->setPartialDate("in_exile_since", 1952, 9, 19);
    $c->setPartialDate("end_of_exile", 1972, 4, 10);
    $c->charges = "U.S. re-entry permit revoked by Attorney General James P. McGranery on September 19, 1952, citing moral turpitude and Communist sympathies, barring Chaplin from returning to the United States after 40 years of residence.";
    $c->convicted = "Barred from re-entry to the United States, 1952";
    $c->sentence = "Barred from setting foot in the United States; he settled at the Manoir de Ban in Vevey, Switzerland and did not return until April 1972, when he received an Honorary Academy Award.";
    $c->save();
    echo "Chaplin: imprisoned_for_days={$c->imprisoned_for_days}, in_exile_for_days={$c->in_exile_for_days}\n";
} else {
    echo "Chaplin has no case row.\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
