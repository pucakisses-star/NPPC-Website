#!/usr/bin/env bash
#
# Remove Alexander Manly and Frank Manly from the prisoner list. The Manly
# brothers, co-publishers of the Wilmington Daily Record, were driven into
# exile by the November 1898 white-supremacist coup — they were never
# arrested or imprisoned, so per the site owner they don't belong on the
# political-prisoner list. Deletes each prisoner, their cases, and any
# stored photo file.
#
# Idempotent: skips anyone already gone. Run from the repo root:
#   bash database/data/remove-manly-brothers.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
foreach (["alexander-manly", "frank-manly"] as $slug) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "  {$slug}: already gone\n"; continue; }
    $p->cases()->delete();
    $photo = storage_path("app/public/prisoners/{$slug}.jpg");
    if (is_file($photo)) { @unlink($photo); }
    $name = $p->name;
    $p->delete();
    echo "  removed {$name} ({$slug})\n";
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Manly brothers removed from the prisoner list."
