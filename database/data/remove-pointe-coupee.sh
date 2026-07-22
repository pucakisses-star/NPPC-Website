#!/usr/bin/env bash
#
# Remove two Pointe Coupee Conspiracy records at the user's request:
#   - antoine-sarrasin (Antoine Sarrasin)
#   - jean-baptiste (Jean-Baptiste)
#
# Both are enslaved leaders of the 1795 Pointe Coupee Conspiracy in Spanish
# Louisiana. Deletes each prisoner and its case(s).
#
# Guarded so it only fires on the exact records: the slug must match AND the
# record must carry the "Pointe Coupee Conspiracy" affiliation (this protects
# the common name Jean-Baptiste from ever matching an unrelated namesake).
# Idempotent: if already removed, it does nothing. Run from the repo root:
#   bash database/data/remove-pointe-coupee.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$slugs = ["antoine-sarrasin", "jean-baptiste"];
$removed = 0;
foreach ($slugs as $slug) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "{$slug}: not found (already removed).\n"; continue; }

    $aff = array_map("strtolower", (array) $p->affiliation);
    $ok = false;
    foreach ($aff as $a) { if (str_contains($a, "pointe coup")) { $ok = true; break; } }
    if (! $ok) {
        echo "{$slug}: does not carry the Pointe Coupee affiliation; NOT deleting.\n";
        continue;
    }

    $n = \App\Models\PrisonerCase::where("prisoner_id", $p->id)->count();
    \App\Models\PrisonerCase::where("prisoner_id", $p->id)->delete();
    $p->delete();
    echo "Removed {$slug} ({$p->name}) and {$n} case(s).\n";
    $removed++;
}

echo "\nRemoved {$removed} record(s).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Pointe Coupee records removed."
