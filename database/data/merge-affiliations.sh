#!/usr/bin/env bash
#
# Consolidate duplicate/misplaced affiliation labels. Map in
# database/data/affiliation-merge-map.json:
#
#   RENAME (merge same-org variants):
#     Industrial Workers of the World -> Industrial Workers of the World (IWW)
#     Plowshares                      -> Plowshares Movement
#     Anarchism                       -> Anarchist Movement
#
#   REMOVE (an ideology mistakenly used as an affiliation):
#     Prison Movement
#
# Case-insensitive; only the affiliation field is touched; duplicates that
# result from a merge are collapsed. Numbered/specific action affiliations
# (e.g. Kings Bay Plowshares 7) are NOT affected. Idempotent. Run from the repo
# root:
#   bash database/data/merge-affiliations.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$map = json_decode(file_get_contents(base_path("database/data/affiliation-merge-map.json")), true);
if (! is_array($map)) { echo "Could not read affiliation map JSON.\n"; return; }
$rename = $map["rename"] ?? [];
$remove = array_map(fn ($v) => strtolower(trim((string) $v)), (array) ($map["remove"] ?? []));

$changed = 0;
\App\Models\Prisoner::withoutGlobalScopes()
    ->select("id", "affiliation")
    ->chunk(500, function ($chunk) use (&$changed, $rename, $remove) {
        foreach ($chunk as $p) {
            $aff = (array) $p->affiliation;
            $new = [];
            foreach ($aff as $v) {
                $v = (string) $v;
                $key = strtolower(trim($v));
                if (in_array($key, $remove, true)) { continue; }
                $new[] = $rename[$key] ?? $v;
            }
            $new = array_values(array_unique($new));
            if ($new !== $aff) {
                $p->affiliation = $new ?: null;
                $p->save();
                $changed++;
            }
        }
    });

echo "Consolidated affiliations on {$changed} record(s).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Affiliations consolidated."
