#!/usr/bin/env bash
#
# Remove two FLQ air-piracy records at the user's request:
#   - jean-pierre-charette (Jean-Pierre Charette)
#   - alain-allard (Alain Allard)
#
# Both hijacked National Airlines Flight 91 to Havana in 1969 and are tagged
# with the Front de liberation du Quebec (FLQ) affiliation. Deletes each
# prisoner and its case(s).
#
# Guarded so it only fires on the exact records: the slug must match AND the
# record must carry the FLQ affiliation. Idempotent: if already removed, it
# does nothing. Run from the repo root:
#   bash database/data/remove-flq-hijackers.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$slugs = ["jean-pierre-charette", "alain-allard"];
$removed = 0;
foreach ($slugs as $slug) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "{$slug}: not found (already removed).\n"; continue; }

    $aff = array_map("strtolower", (array) $p->affiliation);
    $ok = false;
    foreach ($aff as $a) { if (str_contains($a, "front de lib") && str_contains($a, "quebec")) { $ok = true; break; } }
    if (! $ok) {
        echo "{$slug}: does not carry the FLQ affiliation; NOT deleting.\n";
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
echo "Done. FLQ hijacker records removed."
