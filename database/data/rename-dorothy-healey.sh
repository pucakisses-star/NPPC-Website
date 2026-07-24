#!/usr/bin/env bash
#
# Rename the "Dorothy Healey" record to "Dorothy Ray Healey" and drop the old
# name/aka. The slug (dorothy-healey) is left unchanged so existing links keep
# working; only the displayed name changes. This also lets the freely-licensed
# Wikimedia photo (attached by prisoners:attach-wikipedia-photos, which matches
# by name) find her under the new name.
#
# Idempotent. Run from the repo root:
#   bash database/data/rename-dorothy-healey.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "dorothy-healey")->first();
if (! $p) { echo "dorothy-healey not found.\n"; return; }

$p->name = "Dorothy Ray Healey";
$p->first_name = "Dorothy";
$p->middle_name = "Ray";
$p->last_name = "Healey";
$p->aka = null;
$p->save();

$p->refresh();
echo "Renamed to {$p->name} (slug {$p->slug}, aka cleared).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Dorothy Ray Healey renamed."
