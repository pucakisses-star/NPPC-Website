#!/usr/bin/env bash
#
# Populate the individual name parts for Sam Coleman to match his full legal
# name, Samuel Irving Coleman. The display name is left as "Sam Coleman"; only
# the first / middle / last fields are set (same approach as Max Obuszewski).
#
# Sam Coleman is the Communist Party official convicted in San Francisco in 1954
# of harboring Smith Act fugitives; he was one of the petitioners in Kremen v.
# United States, 353 U.S. 346 (1957), which reversed the convictions over the
# FBI'"'"'s warrantless seizure of the Twain Harte cabin'"'"'s contents. The FBI
# summary'"'"'s redacted subject (convicted May 3, 1954 under 18 U.S.C. sec. 3, 371
# and 1071, three-year sentence, represented by Norman Leonard on appeal) is
# this same man, Samuel Irving Coleman.
#
# Idempotent. Run from the repo root:
#   bash database/data/split-sam-coleman-name.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()
    ->where("slug", "sam-coleman")
    ->orWhereRaw("LOWER(name) = ?", ["sam coleman"])
    ->first();

if (! $p) {
    echo "Sam Coleman not found.\n";
} else {
    $p->first_name = "Samuel";
    $p->middle_name = "Irving";
    $p->last_name = "Coleman";
    $p->save();
    echo "SET name parts on {$p->name} (slug: {$p->slug}): Samuel / Irving / Coleman.\n";
    \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
}
echo "Done.\n";
'

echo
echo "Done. Sam Coleman name parts set to Samuel Irving Coleman."
