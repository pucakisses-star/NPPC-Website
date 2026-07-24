#!/usr/bin/env bash
#
# Fix John Berry (director): he was never arrested or imprisoned. He was named
# in HUAC testimony and blacklisted in 1951, fled to France, and lived in exile
# until returning to the U.S. in 1973. His record wrongly stored the exile in
# the incarceration fields (arrest/incarceration 1951-03-31, release 1972-12-31),
# so the page showed "INCARCERATED" and "IMPRISONED FOR 21 YEARS".
#
# This moves it to the exile fields: clears arrest/incarceration/release, sets
# in_exile_since = 1951 and end_of_exile = 1973 (year precision), and flags the
# prisoner as a former exile (not in custody, not currently in exile). The page
# will then read "IN EXILE FOR ~22 YEARS" instead of imprisonment.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-john-berry-exile.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "john-berry-director")->first();
if (! $p) { echo "john-berry-director not found.\n"; return; }

$p->in_exile = true;
$p->currently_in_exile = false;   // returned to the U.S. in 1973
$p->in_custody = false;
$p->released = false;             // he was never a prisoner to be released
$p->save();

$c = $p->cases()->first();
if (! $c) { echo "No case found.\n"; return; }

// Clear the imprisonment fields (he was neither arrested nor jailed).
$c->arrest_date = null;
$c->incarceration_date = null;
$c->release_date = null;
$c->imprisoned_for_days = null;

// Record the exile instead (1951 - 1973, year precision).
$c->setPartialDate("in_exile_since", 1951);
$c->setPartialDate("end_of_exile", 1973);

// Keep the descriptive charges/sentence, but make the sentence label accurate.
$c->convicted = "No — never charged; named in HUAC testimony and industry blacklist (1951)";
$c->charges = ["No criminal charges — named in HUAC testimony and blacklisted (1951)"];
$c->sentence = "Effective political exile, roughly 1951-1973 (~22 years)";
$c->save();

$p->refresh();
echo "Fixed john-berry-director: in exile 1951-1973 (in_exile_for_days={$c->in_exile_for_days}); imprisonment fields cleared.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. John Berry re-classified from imprisonment to exile."
