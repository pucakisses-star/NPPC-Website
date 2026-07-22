#!/usr/bin/env bash
#
# Remove the incorrect photo on the Anthony Mullaney record.
#
# The attached image (anthony-mullaney-b7d981f3-...jpg) is a modern
# booking-style mugshot of a young man with a goatee — a namesake scraped by
# mistake, NOT Father Anthony Mullaney, the 39-year-old Benedictine priest of
# St. Anselm's Abbey who was one of the Milwaukee 14 (1968 draft-board raid).
# Displaying an unrelated person as a political prisoner is a misattribution,
# so the wrong photo is cleared until a correct one is found.
#
# Guarded so it only fires on the known-wrong file. Idempotent: if the photo is
# already cleared or replaced, it does nothing. Run from the repo root:
#   bash database/data/clear-mullaney-wrong-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "anthony-mullaney")->first();
if (! $p) { echo "anthony-mullaney: not found.\n"; return; }

$wrong = "b7d981f3-8a65-4e44-ac2e-9b6ab4720d2a";
if (! empty($p->photo) && str_contains((string) $p->photo, $wrong)) {
    $p->photo = null;
    $p->save();
    echo "Cleared the misattributed Mullaney photo.\n";
} else {
    echo "No matching wrong photo present (already cleared or replaced); nothing to do.\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Incorrect Anthony Mullaney photo removed."
