#!/usr/bin/env bash
#
# Remove "Free Speech" as an ideology: strip it from every prisoner that carries
# it (any capitalization), which also drops it from the ideology filter.
#
# Idempotent. Run from the repo root:
#   bash database/data/remove-free-speech-ideology.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$n = 0;
foreach (\App\Models\Prisoner::withoutGlobalScopes()->whereNotNull("ideologies")->get() as $p) {
    $ids = $p->ideologies ?? [];
    if (! is_array($ids)) { continue; }
    $new = array_values(array_filter($ids, function ($x) { return strcasecmp(trim((string) $x), "free speech") !== 0; }));
    if (count($new) !== count($ids)) {
        $p->ideologies = $new ?: null;
        $p->save();
        echo "Removed from {$p->name}.\n";
        $n++;
    }
}
echo "\nFree Speech ideology removed from {$n} record(s).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Free Speech ideology removed."
