#!/usr/bin/env bash
#
# Remove "Asian American Movement" as an ideology: strip it from every prisoner
# that carries it (any capitalization), dropping it from the ideology filter.
#
# Idempotent. Run from the repo root:
#   bash database/data/remove-asian-american-movement-ideology.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$n = 0;
foreach (\App\Models\Prisoner::withoutGlobalScopes()->whereNotNull("ideologies")->get() as $p) {
    $ids = $p->ideologies ?? [];
    if (! is_array($ids)) { continue; }
    $new = array_values(array_filter($ids, function ($x) { return strcasecmp(trim((string) $x), "asian american movement") !== 0; }));
    if (count($new) !== count($ids)) {
        $p->ideologies = $new ?: null;
        $p->save();
        echo "Removed from {$p->name}.\n";
        $n++;
    }
}
echo "\nAsian American Movement ideology removed from {$n} record(s).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Asian American Movement ideology removed."
