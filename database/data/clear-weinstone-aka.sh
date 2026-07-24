#!/usr/bin/env bash
#
# Remove the "aka" on William W. Weinstone (it currently shows the redundant
# alias "William Weinstone").
#
# Idempotent. Run from the repo root:
#   bash database/data/clear-weinstone-aka.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = null;
foreach (["william-w-weinstone","william-weinstone"] as $s) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $s)->first();
    if ($p) break;
}
if (! $p) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) LIKE ?", ["william%weinstone"])->first();
}

if (! $p) {
    echo "William W. Weinstone not found.\n";
} elseif (empty($p->aka)) {
    echo "{$p->name} already has no aka — nothing to do.\n";
} else {
    echo "Removing aka ".var_export($p->aka, true)." from {$p->name} (slug: {$p->slug}).\n";
    $p->aka = null;
    $p->save();
    \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
}
echo "Done.\n";
'

echo
echo "Done. William W. Weinstone aka removed."
