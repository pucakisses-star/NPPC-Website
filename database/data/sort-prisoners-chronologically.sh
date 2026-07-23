#!/usr/bin/env bash
#
# Re-rank the prisoner database sort_order so the /database list reads
# reverse-chronologically by era (NEWEST first).
#
# The public list (and the /api/prisoners payload it is built from) is ordered
# purely by sort_order. New records default to sort_order 0, so additions
# surfaced out of place instead of alongside their era.
#
# This assigns a fresh sort_order to every prisoner: primary key is the era
# decade taken from the era string ("2020s" -> 2020, "1970s" -> 1970), ordered
# newest-to-oldest. Within a decade the existing relative order is preserved (a
# stable sort), so only cross-era placement changes. Records with no era are
# parked at the very end, keeping their current order.
#
# Idempotent: re-running produces the same ranking. Run from the repo root:
#   bash database/data/sort-prisoners-chronologically.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$rows = \App\Models\Prisoner::withoutGlobalScopes()
    ->select("id", "era", "sort_order")
    ->orderBy("sort_order")->orderBy("id")->get();

// Build sortable entries; preserve current order as the within-decade tiebreak.
$items = [];
foreach ($rows as $i => $p) {
    $dec = null;
    if ($p->era && preg_match("/(1[6-9]\d\d|20\d\d)/", (string) $p->era, $m)) {
        $dec = ((int) floor(((int) $m[1]) / 10)) * 10;
    }
    $items[] = [
        "id" => $p->id,
        "noEra" => $dec === null ? 1 : 0,   // no-era records sort last
        "dec" => $dec ?? 0,
        "so" => (int) $p->sort_order,
        "idx" => $i,
    ];
}

// No-era last; then decade DESCENDING (newest first); then preserve current
// within-decade order via idx.
usort($items, function ($a, $b) {
    return [$a["noEra"], -$a["dec"], $a["idx"]] <=> [$b["noEra"], -$b["dec"], $b["idx"]];
});

$i = 0; $changed = 0; $decadeCounts = [];
foreach ($items as $it) {
    if ($it["so"] !== $i) {
        \App\Models\Prisoner::withoutGlobalScopes()->where("id", $it["id"])->update(["sort_order" => $i]);
        $changed++;
    }
    $label = $it["noEra"] ? "(no era)" : ($it["dec"] . "s");
    $decadeCounts[$label] = ($decadeCounts[$label] ?? 0) + 1;
    $i++;
}

echo "Ranked " . count($items) . " prisoners newest-first; {$changed} sort_order value(s) changed.\n\n";
echo "Order now runs:\n";
foreach ($decadeCounts as $label => $n) { echo "  {$label}: {$n}\n"; }

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done. Prisoner database re-sorted newest-first by era."
