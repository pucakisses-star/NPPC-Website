#!/usr/bin/env bash
#
# Clear the now-redundant aka on Max Obuszewski. An earlier update set
# aka = "Max Obuszewski" back when the display name was the fuller
# "Maximilian J. \"Max\" Obuszewski"; the display name is now just
# "Max Obuszewski", so the alias duplicates it and should be empty.
#
# Idempotent. Run from the repo root:
#   bash database/data/clear-obuszewski-aka.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$m = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "max-obuszewski")->first();
if ($m && ! empty($m->aka)) {
    $m->aka = null;
    $m->save();
    echo "CLEARED aka on max-obuszewski\n";
} else {
    echo "Nothing to do.\n";
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Max Obuszewski aka cleared."
