#!/usr/bin/env bash
#
# Remove two City Lights "Howl" obscenity-case records at the user's request:
#   - lawrence-ferlinghetti (Lawrence Ferlinghetti)
#   - shigeyoshi-murao (Shigeyoshi "Shig" Murao)
#
# Both were defendants in the 1957 San Francisco obscenity prosecution over
# City Lights publishing/selling Allen Ginsberg's "Howl"; the case ended in
# acquittal and neither served real jail time. Deletes each prisoner and its
# case(s).
#
# Guarded so it only fires on the exact records: the slug must match AND the
# record must carry the "City Lights" affiliation. Idempotent: if already
# removed, it does nothing. Run from the repo root:
#   bash database/data/remove-city-lights-howl.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$slugs = ["lawrence-ferlinghetti", "shigeyoshi-murao"];
$removed = 0;
foreach ($slugs as $slug) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "{$slug}: not found (already removed).\n"; continue; }

    $aff = array_map("strtolower", (array) $p->affiliation);
    if (! in_array("city lights", $aff, true)) {
        echo "{$slug}: does not carry the City Lights affiliation; NOT deleting.\n";
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
echo "Done. City Lights Howl records removed."
