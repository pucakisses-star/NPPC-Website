#!/usr/bin/env bash
#
# Flag as "minor cases" every prisoner whose TOTAL recorded time in jail is under
# 15 days. Prisoners with no computed jail time yet (no case has an
# imprisoned_for_days value) are ignored — not flagged either way. Existing
# minor_case flags are only turned ON; nothing is turned off.
#
# Total time = sum of imprisoned_for_days across the prisoner's cases (0-day
# same-day arrests count as 0). Idempotent. Run from the repo root:
#   bash database/data/flag-minor-cases-under-15-days.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$set = 0; $already = 0; $ignored = 0; $over = 0;
foreach (\App\Models\Prisoner::withoutGlobalScopes()->with("cases")->get() as $p) {
    // Days that are actually set (keep 0, drop null).
    $days = $p->cases->pluck("imprisoned_for_days")->filter(function ($d) { return $d !== null; });
    if ($days->isEmpty()) { $ignored++; continue; }   // no time set yet -> ignore

    $total = (int) $days->sum();
    if ($total < 15) {
        if ($p->minor_case) { $already++; }
        else { $p->minor_case = true; $p->save(); echo "minor: {$p->name} ({$total} days).\n"; $set++; }
    } else {
        $over++;
    }
}
echo "\nNewly flagged minor: {$set}\n";
echo "Already minor: {$already}\n";
echo "Not minor (>=15 days): {$over}\n";
echo "Ignored (no jail time set): {$ignored}\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Minor-case flags set for prisoners under 15 days."
