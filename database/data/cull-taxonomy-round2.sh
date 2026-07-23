#!/usr/bin/env bash
#
# Second-round taxonomy cleanup (after the singleton removal):
#
#   1. MERGE synonym pairs (fold the smaller label into the larger):
#        Quaker            -> Quakerism
#        Anti-colonialism  -> Anti-colonial
#        Labor movement    -> Labor organizing
#   2. CULL vague, non-ideology tags from the ideologies field:
#        Bohemian, Counterculture, Free love, Progressive,
#        Self-described revolutionary, Rule of law, Political dissent,
#        Human rights
#   3. SWAP organization names that were miscategorized as ideologies so they
#      become affiliations instead (removed from ideologies, added to
#      affiliation if not already present):
#        Posse Comitatus, MOVE, Symbionese Liberation Army,
#        Earth Liberation Front, Plowshares
#
# Low-count affiliations were reviewed and left alone: nearly all are real
# organizations (ACLU, ACT UP, Nation of Islam, Young Lords, etc.).
#
# Idempotent: once the source values are gone, a re-run finds nothing to do.
# Run from the repo root:
#   bash database/data/cull-taxonomy-round2.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$mergeMap = ["Quaker" => "Quakerism", "Anti-colonialism" => "Anti-colonial", "Labor movement" => "Labor organizing"];
$vague = array_flip(["Bohemian", "Counterculture", "Free love", "Progressive", "Self-described revolutionary", "Rule of law", "Political dissent", "Human rights"]);
$orgSwap = array_flip(["Posse Comitatus", "MOVE", "Symbionese Liberation Army", "Earth Liberation Front", "Plowshares"]);

$records = 0; $merged = 0; $culled = 0; $swapped = 0;
\App\Models\Prisoner::withoutGlobalScopes()->select("id", "slug", "ideologies", "affiliation")
    ->chunk(500, function ($chunk) use ($mergeMap, $vague, $orgSwap, &$records, &$merged, &$culled, &$swapped) {
    foreach ($chunk as $p) {
        $ideol = (array) $p->ideologies;
        $aff = (array) $p->affiliation;
        $newIdeol = []; $addAff = []; $dirty = false;
        foreach ($ideol as $v) {
            $v = trim((string) $v);
            if ($v === "") { continue; }
            if (isset($mergeMap[$v])) { $newIdeol[] = $mergeMap[$v]; $merged++; $dirty = true; }
            elseif (isset($vague[$v])) { $culled++; $dirty = true; }
            elseif (isset($orgSwap[$v])) { $addAff[] = $v; $swapped++; $dirty = true; }
            else { $newIdeol[] = $v; }
        }
        if (! $dirty) { continue; }
        $newIdeol = array_values(array_unique($newIdeol));
        $newAff = $aff;
        foreach ($addAff as $o) { if (! in_array($o, $newAff, true)) { $newAff[] = $o; } }
        $newAff = array_values(array_unique($newAff));
        $p->ideologies = $newIdeol ?: null;
        $p->affiliation = $newAff ?: null;
        $p->save();
        $records++;
    }
});

echo "Updated {$records} record(s): {$merged} synonym merge(s), {$culled} vague tag(s) removed, {$swapped} org tag(s) moved to affiliation.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Round-two taxonomy cleanup applied."
