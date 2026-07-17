#!/usr/bin/env bash
#
# Removes the Kim Dae-jung record (slug kim-dae-jung). He was a South Korean
# political prisoner (and later President of South Korea) — outside the
# site's scope of US political prisoners. His case rows are removed with him
# (the prisoner_cases FK cascades); any podcast/calendar references are set
# null by their FKs.
#
# Idempotent: prints NOTHING-TO-DO if the record is already gone.
#
# Run from the repo root:  bash database/data/remove-kim-dae-jung.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "kim-dae-jung")->first()
  ?? \App\Models\Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) IN (?, ?)", ["kim dae jung", "kim dae-jung"])->first();
if (! $p) {
    echo "NOTHING-TO-DO: Kim Dae-jung record not found (already removed).\n";
} else {
    $cases = $p->cases()->count();
    echo "DELETE {$p->slug} ({$p->name}) with {$cases} case row(s)\n";
    $p->delete();
    \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
    echo "Done.\n";
}
'
