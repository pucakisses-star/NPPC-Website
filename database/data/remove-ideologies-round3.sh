#!/usr/bin/env bash
#
# Strip these eight values from every prisoner's ideologies list (other tags
# kept; no records deleted). List in database/data/ideology-remove-round3.json:
#   Anti-Corruption, Anti-Debt Courts, Anti-Capitalism, Anti-Government,
#   Confederate Sympathies, Mormonism, Nationalism, Houseless Rights
#
# Case-insensitive, whitespace-trimmed. Idempotent. Run from the repo root:
#   bash database/data/remove-ideologies-round3.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$remove = array_map(fn ($v) => strtolower(trim((string) $v)),
    (array) json_decode(file_get_contents(base_path("database/data/ideology-remove-round3.json")), true));

$changed = 0;
\App\Models\Prisoner::withoutGlobalScopes()
    ->select("id", "ideologies")
    ->chunk(500, function ($chunk) use (&$changed, $remove) {
        foreach ($chunk as $p) {
            $ide = (array) $p->ideologies;
            $kept = array_values(array_filter($ide, fn ($v) => ! in_array(strtolower(trim((string) $v)), $remove, true)));
            if (count($kept) !== count($ide)) {
                $p->ideologies = $kept ?: null;
                $p->save();
                $changed++;
            }
        }
    });

echo "Removed the eight ideologies from {$changed} record(s).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Round-3 ideology removals applied."
