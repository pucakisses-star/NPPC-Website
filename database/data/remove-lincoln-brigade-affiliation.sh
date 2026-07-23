#!/usr/bin/env bash
#
# Remove "Abraham Lincoln Brigade" from the affiliation list at the user's
# request. It is stripped from every prisoner whose affiliation array contains
# it (comparison is case-insensitive and trims whitespace); all other
# affiliations on those records are kept. No records are deleted.
#
# Idempotent: re-running does nothing once the tag is gone. Run from the repo
# root:
#   bash database/data/remove-lincoln-brigade-affiliation.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$target = "abraham lincoln brigade";
$changed = 0;
\App\Models\Prisoner::withoutGlobalScopes()
    ->select("id", "affiliation")
    ->chunk(500, function ($chunk) use (&$changed, $target) {
        foreach ($chunk as $p) {
            $aff = (array) $p->affiliation;
            $kept = array_values(array_filter($aff, function ($v) use ($target) {
                return strtolower(trim((string) $v)) !== $target;
            }));
            if (count($kept) !== count($aff)) {
                $p->affiliation = $kept ?: null;
                $p->save();
                $changed++;
            }
        }
    });

echo "Removed the Abraham Lincoln Brigade affiliation from {$changed} record(s).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Abraham Lincoln Brigade affiliation removed."
