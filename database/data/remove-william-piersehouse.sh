#!/usr/bin/env bash
#
# Remove the William Piersehouse prisoner record (early Quaker conscientious
# objector from Besse's sufferings collection), deleting his cases and the
# prisoner itself.
#
# Idempotent. Run from the repo root:
#   bash database/data/remove-william-piersehouse.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()
    ->where("slug", "william-piersehouse")
    ->orWhereRaw("LOWER(name) = ?", ["william piersehouse"])
    ->first();

if (! $p) {
    echo "William Piersehouse not found (already removed?).\n";
} else {
    $slug = $p->slug; $name = $p->name; $id = $p->id;
    $p->cases()->delete();
    $p->delete();
    echo "Removed prisoner {$name} (slug: {$slug}, id: {$id}) and any cases.\n";
    \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
}
echo "Done.\n";
'

echo
echo "Done. William Piersehouse removed."
