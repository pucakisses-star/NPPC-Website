#!/usr/bin/env bash
#
# Links sammelville.org — the historical archive about Sam Melville
# maintained by people who knew him, covering his bombing campaign,
# his Attica organizing, and his 1971 death — as the website on his
# prisoner record. Fill-if-empty.
#
# Run from the repo root:  bash database/data/set-melville-website.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "sam-melville")->first();
if (! $p) { echo "sam-melville not found\n"; exit(1); }
if (empty($p->website)) {
    $p->website = "https://sammelville.org/";
    $p->save();
    echo "WEBSITE set on sam-melville\n";
} else {
    echo "Website already set: {$p->website} (unchanged)\n";
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Sam Melville website linked."
