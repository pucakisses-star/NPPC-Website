#!/usr/bin/env bash
#
# Ideology taxonomy cleanup (round 2), per request. Mapping in
# database/data/ideology-cleanup-map.json:
#
#   REMOVE (stripped from every record; nothing added):
#     Unemployed Movement, Street Vendor Rights, States' Rights,
#     Salafi/Jihadist/Islamist, Revolutionary Nationalism, Occupy Movement,
#     Loyalism, Lesbian Feminism, Liberation Theology, Libertarianism, Islam,
#     Industrial Unionism, Greenback-Labor, Feminism, Financial Privacy,
#     Free Software, Farm Organizing, Direct Action, Christian Pacifism,
#     Christian Right, Civil Disobedience
#
#   RENAME:
#     LGBTQ Liberation -> LGBT Activism
#     Catholic         -> Catholic Worker Movement
#
# Matching is case-insensitive. Removals are applied first, then renames; any
# duplicate a rename creates is collapsed. Only the ideologies field is touched.
# Idempotent. Run from the repo root:
#   bash database/data/cleanup-ideologies-round2.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$map = json_decode(file_get_contents(base_path("database/data/ideology-cleanup-map.json")), true);
if (! is_array($map)) { echo "Could not read cleanup map JSON.\n"; return; }
$remove = array_map(fn ($v) => strtolower(trim((string) $v)), (array) ($map["remove"] ?? []));
$rename = $map["rename"] ?? [];

$changed = 0;
\App\Models\Prisoner::withoutGlobalScopes()
    ->select("id", "ideologies")
    ->chunk(500, function ($chunk) use (&$changed, $remove, $rename) {
        foreach ($chunk as $p) {
            $ide = (array) $p->ideologies;
            $new = [];
            foreach ($ide as $v) {
                $v = (string) $v;
                $key = strtolower(trim($v));
                if (in_array($key, $remove, true)) { continue; }
                $new[] = $rename[$key] ?? $v;
            }
            $new = array_values(array_unique($new));
            if ($new !== $ide) {
                $p->ideologies = $new ?: null;
                $p->save();
                $changed++;
            }
        }
    });

echo "Cleaned ideologies on {$changed} record(s).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Ideology cleanup (round 2) applied."
