#!/usr/bin/env bash
#
# Clear the hacker online handles that were sitting in the aka field of the
# Anonymous/LulzSec prisoners, so their entries show only their normal names
# (the name field already holds the real name). Removes:
#   Sabu, recursion, neuron, w0rmer, AkronPhoenix420
# The handles still appear in each person's description prose, so no
# information is lost.
#
# Idempotent: only clears aka while it is still set.
#   bash database/data/clear-hacker-handle-akas.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$slugs = [
    "hector-xavier-monsegur",
    "cody-andrew-kretsinger",
    "raynaldo-rivera",
    "higinio-ochoa-iii",
    "james-e-robinson",
];
foreach ($slugs as $slug) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "  {$slug}: NOT FOUND\n"; continue; }
    if (! empty($p->aka)) {
        $old = $p->aka;
        $p->aka = null;
        $p->save();
        echo "  {$slug}: cleared aka (was \"{$old}\")\n";
    } else {
        echo "  {$slug}: aka already empty\n";
    }
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Hacker-handle akas cleared."
