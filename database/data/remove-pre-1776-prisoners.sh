#!/usr/bin/env bash
#
# Remove prisoners whose imprisonment began before 1776.
#
# "Imprisoned before 1776" = the earliest incarceration_date (falling back to
# arrest_date) across the prisoner's cases is before 1776-01-01. Prisoners with
# no dated case are NOT removed -- they are listed separately for manual review,
# since we cannot confirm when (or whether) they were imprisoned.
#
# This catches e.g. the 1741 New York conspiracy roster, the Virginia Baptist
# ministers (1768-1774) and the North Carolina Regulators (1771). It KEEPS the
# Fort Randolph hostages (1777), the St. Augustine detainees (1780), Shays
# (1787) and the Whiskey Rebellion (1794).
#
# SAFE BY DEFAULT:
#   * With no flags it is a DRY RUN -- it only lists what would be removed.
#   * APPLY=1 actually applies the change.
#   * MODE=unpublish (default) is REVERSIBLE: it sets under_review=true so the
#     records leave the public site but stay in the database.
#   * MODE=delete HARD-DELETES the prisoners and their cases (cascade). Not
#     recoverable without a database restore.
#
# Usage from the repo root:
#   bash database/data/remove-pre-1776-prisoners.sh                 # dry run
#   APPLY=1 bash database/data/remove-pre-1776-prisoners.sh         # unpublish
#   APPLY=1 MODE=delete bash database/data/remove-pre-1776-prisoners.sh  # delete

set -euo pipefail
cd "$(dirname "$0")/../.."

APPLY="${APPLY:-0}" MODE="${MODE:-unpublish}" php artisan tinker --execute='
use App\Models\Prisoner;
use Carbon\Carbon;

$cutoff = Carbon::parse("1776-01-01");
$apply  = getenv("APPLY") === "1";
$mode   = getenv("MODE") ?: "unpublish";
if (! in_array($mode, ["unpublish", "delete"], true)) { echo "Bad MODE (use unpublish|delete)\n"; return; }

$inScope = []; $undated = [];
foreach (Prisoner::withoutGlobalScopes()->with("cases")->get() as $p) {
    $earliest = null;
    foreach ($p->cases as $c) {
        foreach (["incarceration_date", "arrest_date"] as $f) {
            if ($c->{$f}) {
                $dt = Carbon::parse($c->{$f});
                if (! $earliest || $dt->lt($earliest)) { $earliest = $dt; }
            }
        }
    }
    if (! $earliest) {
        if (in_array($p->era, ["Colonial America", "American Revolution", "Early Republic"], true)) { $undated[] = $p; }
        continue;
    }
    if ($earliest->lt($cutoff)) { $inScope[] = [$p, $earliest]; }
}

usort($inScope, fn ($a, $b) => $a[1] <=> $b[1]);

echo "=== Prisoners imprisoned before 1776 (".count($inScope)." in scope) ===\n";
foreach ($inScope as [$p, $earliest]) {
    echo "  ".str_pad($p->slug, 32)." | ".str_pad($p->name, 26)." | ".$earliest->format("Y-m-d")." | ".($p->era ?? "-").($p->under_review ? " [already unpublished]" : "")."\n";
}

if ($undated) {
    echo "\n--- Undated colonial-era records (NOT removed; review manually): ".count($undated)." ---\n";
    foreach ($undated as $p) { echo "  ".str_pad($p->slug, 32)." | ".str_pad($p->name, 26)." | ".($p->era ?? "-")."\n"; }
}

if (! $apply) {
    echo "\nDRY RUN -- nothing changed. Re-run with APPLY=1 (MODE=unpublish reversible, or MODE=delete permanent).\n";
    return;
}

$done = 0;
foreach ($inScope as [$p, $earliest]) {
    if ($mode === "delete") {
        $p->delete();
        $done++;
    } else {
        if (! $p->under_review) { $p->under_review = true; $p->save(); $done++; }
    }
}

echo "\n=== Applied: mode={$mode}, affected={$done} ===\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
