#!/usr/bin/env bash
#
# Remove the "Zapatista solidarity" and "Wolf Clan" affiliation tags from
# Byron Chubbuck's record, per site-owner direction. Any other affiliations
# he may carry are preserved.
#
# Idempotent: re-runs are no-ops once the tags are gone.
#   bash database/data/clean-byron-chubbuck-affiliation.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "byron-chubbuck")->first();
if (! $p) { echo "byron-chubbuck not found\n"; return; }

$remove = ["Zapatista solidarity", "Wolf Clan"];
$aff = $p->affiliation ?? [];
if (! is_array($aff)) { $aff = $aff === null ? [] : [$aff]; }

$kept = array_values(array_filter($aff, fn ($a) => ! in_array($a, $remove, true)));

if (count($kept) !== count($aff)) {
    $p->affiliation = $kept;
    $p->save();
    echo "UPDATED affiliation -> " . json_encode($kept) . "\n";
} else {
    echo "Nothing to do.\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Byron Chubbuck affiliation cleaned."
