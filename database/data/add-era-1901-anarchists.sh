#!/usr/bin/env bash
#
# Set the era on the eight 1901 Chicago "Free Society" anarchists (arrested
# September 1901 in the roundup after the McKinley assassination) that are
# missing one: marie-isaak, julia-mechanic, abraham-isaak-jr,
# clemens-pfuetzner, alfred-schneider, enrico-travaglio, martin-rasnick,
# michael-roz.
#
# The era value is copied from an already-positioned cluster-mate (Abraham
# Isaak Sr., then Jay Fox, then any positioned Anarchism-affiliated prisoner
# with an era) so it matches the rest of the anarchist cluster exactly;
# falls back to "1900s" if none is found. Only fills empty eras.
#
# Idempotent. Run from the repo root:
#   bash database/data/add-era-1901-anarchists.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

// Find the era used by the positioned anarchist cluster.
$era = null;
foreach (["abraham-isaak", "jay-fox", "hippolyte-havel"] as $slug) {
    $mate = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if ($mate && $mate->era) { $era = $mate->era; echo "era source: {$slug} -> {$era}\n"; break; }
}
if (! $era) {
    $mate = Prisoner::withoutGlobalScopes()
        ->where("sort_order", "!=", 0)
        ->where("affiliation", "like", "%Anarchism%")
        ->whereNotNull("era")->where("era", "!=", "")
        ->orderBy("sort_order")
        ->first();
    if ($mate) { $era = $mate->era; echo "era source: {$mate->slug} -> {$era}\n"; }
}
if (! $era) { $era = "1900s"; echo "era source: fallback -> {$era}\n"; }

$slugs = [
    "marie-isaak", "julia-mechanic", "abraham-isaak-jr", "clemens-pfuetzner",
    "alfred-schneider", "enrico-travaglio", "martin-rasnick", "michael-roz",
];
foreach ($slugs as $slug) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "NOT FOUND: {$slug}\n"; continue; }
    if ($p->era) { echo "{$slug}: already has era {$p->era}, skipped.\n"; continue; }
    $p->era = $era;
    $p->save();
    echo "{$slug}: era set to {$era}.\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
