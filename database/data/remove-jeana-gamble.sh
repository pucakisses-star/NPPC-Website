#!/usr/bin/env bash
#
# Remove the Jeana Gamble record -- arrested in October 2025 at a No Kings
# protest in Fairhope, Alabama, charged with disorderly conduct, resisting
# arrest, disturbing the peace and giving a false name, and acquitted of all
# charges in April 2026.
#
# Matched by slug with a name fallback. The record is printed in full first,
# including any case dates it holds, so the output is a receipt of what was
# removed. Aborts rather than guessing if more than one record matches.
#
# Deletes by default. REVIEW=1 hides it instead (under_review = true), which
# keeps the data and is reversible from the admin.
#
#   bash database/data/remove-jeana-gamble.sh
#   REVIEW=1 bash database/data/remove-jeana-gamble.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

REVIEW="${REVIEW:-0}" php artisan tinker --execute='
use App\Models\Prisoner;

$review = getenv("REVIEW") === "1";

$matches = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->where("slug", "jeana-gamble")
        ->orWhereRaw("LOWER(name) = ?", ["jeana gamble"]))
    ->with("cases")
    ->get();

if ($matches->isEmpty()) { echo "Not found: Jeana Gamble (already removed?)\n"; exit(0); }
if ($matches->count() > 1) {
    echo "ABORT: {$matches->count()} records match. Refusing to guess:\n";
    foreach ($matches as $m) { echo "  {$m->slug}  {$m->name}\n"; }
    exit(1);
}

$p = $matches->first();
echo "{$p->name}  [{$p->slug}]  sort={$p->sort_order}  cases=".$p->cases->count()."\n";
echo "  state:  ".($p->state ?: "-")."  era: ".($p->era ?: "-")."\n";
echo "  photo:  ".($p->photo ?: "(none)")."\n";
foreach ($p->cases as $c) {
    echo "  case inc=".($c->incarceration_date ?: "-")." rel=".($c->release_date ?: "-")
        ." days=".($c->imprisoned_for_days ?? "null")."  ".substr((string) $c->charges, 0, 60)."\n";
}

if ($review) {
    $p->under_review = true;
    $p->save();
    echo "\nHidden from the public site (under_review = true). Data kept; reverse it in the admin.\n";
} else {
    $n = $p->cases()->count();
    $p->delete();
    echo "\nDeleted the record and its {$n} case(s).\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
