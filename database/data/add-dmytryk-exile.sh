#!/usr/bin/env bash
#
# Add Edward Dmytryk's blacklist-era exile period.
#
# Dmytryk (one of the Hollywood Ten) already has his Mill Point imprisonment
# recorded (surrendered June 29, 1950; released November 1950). BEFORE that
# prison term he had gone into self-exile in England, where he directed
# Obsession (1949) and Give Us This Day (1949); he returned to the United
# States in 1950 when his British passport expired, served his sentence, and
# then testified before HUAC as a cooperative witness in April 1951.
#
# This flags him as a former exile and records the exile period 1949-1950
# (year precision) on his existing case, alongside the imprisonment. It leaves
# the Mill Point imprisonment fields and institution untouched, and appends a
# sentence to his description (idempotently) so the narrative matches.
#
# Idempotent. Run from the repo root:
#   bash database/data/add-dmytryk-exile.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "edward-dmytryk")->first();
if (! $p) { echo "edward-dmytryk not found.\n"; return; }

// He returned from exile and later served his prison term, so he is a former
// exile (not currently in exile). Keep the imprisonment/release flags as-is.
$p->in_exile = true;
$p->currently_in_exile = false;

// Add a clarifying sentence about the England exile (idempotent).
$marker = "self-exile in England";
if ($p->description && ! str_contains($p->description, $marker)) {
    $p->description = trim($p->description) . " Before serving that sentence Dmytryk had gone into "
        . "self-exile in England, where he directed Obsession and Give Us This Day (both 1949); when his "
        . "British passport expired he returned to the United States in 1950, served his prison term, and "
        . "then appeared before HUAC as a cooperative witness in April 1951.";
}
$p->save();

$c = $p->cases()->first();
if (! $c) { echo "No case found.\n"; return; }
// Record the exile period 1949-1950 (year precision) on the existing case,
// alongside the Mill Point imprisonment. The saving hook computes
// in_exile_for_days from these dates without touching imprisoned_for_days.
$c->setPartialDate("in_exile_since", 1949);
$c->setPartialDate("end_of_exile", 1950);
$c->save();

$p->refresh();
echo "Updated edward-dmytryk: in exile 1949-1950 (in_exile_for_days={$c->in_exile_for_days}); imprisonment preserved (imprisoned_for_days={$c->imprisoned_for_days}).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Edward Dmytryk exile period added."
