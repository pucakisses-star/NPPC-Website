#!/usr/bin/env bash
#
# Remove the Susan B. Anthony prisoner record, deleting her cases and the
# prisoner itself.
#
# Idempotent. Run from the repo root:
#   bash database/data/remove-susan-b-anthony.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()
    ->where("slug", "susan-b-anthony")
    ->orWhereRaw("LOWER(name) IN (?,?,?)", ["susan b. anthony", "susan b anthony", "susan brownell anthony"])
    ->first();

if (! $p) {
    echo "Susan B. Anthony not found (already removed?).\n";
} else {
    $slug = $p->slug; $name = $p->name; $id = $p->id;
    $p->cases()->delete();
    $p->delete();
    echo "Removed prisoner {$name} (slug: {$slug}, id: {$id}) and any cases.\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Susan B. Anthony removed."
