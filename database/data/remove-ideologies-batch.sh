#!/usr/bin/env bash
#
# Remove four ideologies from every prisoner that carries them (any
# capitalization), dropping them from the ideology filter:
#   Anti-Colonial, AIDS Activism, Anti-Abortion, Latin America Solidarity
#
# Idempotent. Run from the repo root:
#   bash database/data/remove-ideologies-batch.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$remove = ["anti-colonial", "aids activism", "anti-abortion", "latin america solidarity"];

$n = 0;
foreach (\App\Models\Prisoner::withoutGlobalScopes()->whereNotNull("ideologies")->get() as $p) {
    $ids = $p->ideologies ?? [];
    if (! is_array($ids)) { continue; }
    $new = array_values(array_filter($ids, function ($x) use ($remove) {
        return ! in_array(mb_strtolower(trim((string) $x)), $remove, true);
    }));
    if (count($new) !== count($ids)) {
        $p->ideologies = $new ?: null;
        $p->save();
        echo "Cleaned {$p->name}.\n";
        $n++;
    }
}
echo "\nRemoved the four ideologies from {$n} record(s).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Removed Anti-Colonial, AIDS Activism, Anti-Abortion, Latin America Solidarity."
