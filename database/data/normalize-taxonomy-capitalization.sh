#!/usr/bin/env bash
#
# Normalize the capitalization of ideology and affiliation tags to Title Case
# (each significant word capitalized), while preserving acronyms (FLQ, IWW,
# AIDS, ICE, GI, LGBTQ, RNC...), small connector words (of, the, de, du, del,
# from, with...), court-case "v.", and proper names left exactly as-is (e.g.
# "Front de libération du Québec (FLQ)").
#
# The old -> new mapping is in database/data/taxonomy-capitalization-map.json.
# For every prisoner, each ideology/affiliation value is remapped through it;
# all other values are left unchanged. Idempotent (once normalized, values are
# no longer map keys). Run from the repo root:
#   bash database/data/normalize-taxonomy-capitalization.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$map = json_decode(file_get_contents(base_path("database/data/taxonomy-capitalization-map.json")), true);
if (! is_array($map)) { echo "Could not read map JSON.\n"; return; }
$ideMap = $map["ideologies"] ?? [];
$affMap = $map["affiliations"] ?? [];

$remap = function ($list, $m) {
    $out = [];
    foreach ((array) $list as $v) {
        $v = (string) $v;
        $out[] = $m[$v] ?? $v;
    }
    // de-duplicate while preserving order (a remap can collide with an existing value)
    return array_values(array_unique($out));
};

$changed = 0;
\App\Models\Prisoner::withoutGlobalScopes()
    ->select("id", "ideologies", "affiliation")
    ->chunk(500, function ($chunk) use (&$changed, $ideMap, $affMap, $remap) {
        foreach ($chunk as $p) {
            $ide = (array) $p->ideologies;
            $aff = (array) $p->affiliation;
            $newIde = $remap($ide, $ideMap);
            $newAff = $remap($aff, $affMap);
            if ($newIde !== $ide || $newAff !== $aff) {
                $p->ideologies = $newIde ?: null;
                $p->affiliation = $newAff ?: null;
                $p->save();
                $changed++;
            }
        }
    });

echo "Normalized capitalization on {$changed} record(s).\n";
echo "  (" . count($ideMap) . " ideology labels and " . count($affMap) . " affiliation labels remapped.)\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Ideology and affiliation capitalization normalized."
