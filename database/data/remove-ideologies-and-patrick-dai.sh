#!/usr/bin/env bash
#
# Two removals, per request:
#
#  1. Strip these eight values from every prisoner's ideologies list (kept
#     values are untouched; no records deleted by this part):
#       Agrarian populism, Anarcho-Syndicalism, Anti-Authoritarian, Anti-Israel,
#       Anti-Jewish, White supremacy, Trade unionism, Syndicalism
#
#  2. Delete the Patrick Dai prisoner record (and its case[s]) entirely.
#
# Comparison is case-insensitive and whitespace-trimmed. Idempotent. Run from
# the repo root:
#   bash database/data/remove-ideologies-and-patrick-dai.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$remove = array_map("strtolower", [
    "Agrarian populism", "Anarcho-Syndicalism", "Anti-Authoritarian", "Anti-Israel",
    "Anti-Jewish", "White supremacy", "Trade unionism", "Syndicalism",
]);

$changed = 0;
\App\Models\Prisoner::withoutGlobalScopes()
    ->select("id", "ideologies")
    ->chunk(500, function ($chunk) use (&$changed, $remove) {
        foreach ($chunk as $p) {
            $ide = (array) $p->ideologies;
            $kept = array_values(array_filter($ide, function ($v) use ($remove) {
                return ! in_array(strtolower(trim((string) $v)), $remove, true);
            }));
            if (count($kept) !== count($ide)) {
                $p->ideologies = $kept ?: null;
                $p->save();
                $changed++;
            }
        }
    });
echo "Removed the eight ideologies from {$changed} record(s).\n";

// Delete individual prisoner records: Patrick Dai and the two FLQ hijackers
// (Jean-Pierre Charette and Alain Allard). Each is guarded by slug.
$deleteSlugs = ["patrick-dai", "jean-pierre-charette", "alain-allard"];
foreach ($deleteSlugs as $slug) {
    $r = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $r) { echo "{$slug}: not found (already removed).\n"; continue; }
    $n = \App\Models\PrisonerCase::where("prisoner_id", $r->id)->count();
    \App\Models\PrisonerCase::where("prisoner_id", $r->id)->delete();
    $r->delete();
    echo "Deleted {$slug} ({$r->name}) and {$n} case(s).\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Ideologies removed and Patrick Dai deleted."
