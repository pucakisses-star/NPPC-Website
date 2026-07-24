#!/usr/bin/env bash
#
# Consolidate near-synonym ideology tags (combine same-meaning labels into one
# canonical). Mapping in database/data/ideology-merge-map.json:
#
#   Prison Movement, Prisoners' Rights, Anti-Private Prison -> Prison Abolition
#   Native American Rights                                  -> Indigenous Sovereignty
#   Modoc Sovereignty                                       -> Native American Activism
#   Marxism-Leninism, Trotskyism                            -> Marxism
#   Revolutionary Socialism                                 -> Socialism
#   Peace Movement                                          -> Pacifism
#
# Distinct concepts that only look similar are intentionally NOT merged (the
# Black-* family, Anti-War vs Anti-Militarism vs Pacifism, Anti-Colonial vs
# Anti-Imperialism, Civil Rights vs Civil Liberties, etc.).
#
# Matching is case-insensitive so it works before or after the capitalization
# pass. Only the ideologies field is touched; duplicates that result from a
# merge are collapsed. Idempotent. Run from the repo root:
#   bash database/data/merge-ideologies.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$map = json_decode(file_get_contents(base_path("database/data/ideology-merge-map.json")), true);
if (! is_array($map)) { echo "Could not read merge map JSON.\n"; return; }

$changed = 0;
\App\Models\Prisoner::withoutGlobalScopes()
    ->select("id", "ideologies")
    ->chunk(500, function ($chunk) use (&$changed, $map) {
        foreach ($chunk as $p) {
            $ide = (array) $p->ideologies;
            $new = [];
            foreach ($ide as $v) {
                $v = (string) $v;
                $key = strtolower(trim($v));
                $new[] = $map[$key] ?? $v;
            }
            $new = array_values(array_unique($new));
            if ($new !== $ide) {
                $p->ideologies = $new ?: null;
                $p->save();
                $changed++;
            }
        }
    });

echo "Merged near-synonym ideologies on {$changed} record(s).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Ideology near-synonyms consolidated."
