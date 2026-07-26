#!/usr/bin/env bash
#
# Fix the "IMPRISONED FOR 107 YEARS" bug on suffrage prisoners. Records with an
# incarceration date but no release date get their time counted up to today IF
# the prisoner is still flagged in_custody or awaiting_trial. These 1917-1919
# National Woman's Party prisoners are all long released, so this clears those
# flags (in_custody=false, awaiting_trial=false, released=true) and re-saves
# their cases to recompute imprisoned_for_days -- which becomes null (no bogus
# duration) for the cohorts whose exact release day is undocumented.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-suffrage-custody-flags.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$people = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->where("affiliation", "like", "%Woman%Party%")
        ->orWhere("affiliation", "like", "%Silent Sentinel%"))
    ->get();

$flagsFixed = 0; $casesTouched = 0;
foreach ($people as $p) {
    $wasDetained = $p->in_custody || $p->awaiting_trial || ! $p->released;
    $p->in_custody = false;
    $p->awaiting_trial = false;
    $p->released = true;
    $p->save();
    if ($wasDetained) { $flagsFixed++; }
    // Re-save each case so imprisoned_for_days recomputes with the corrected
    // (now-not-detained) flags -- kills the count-to-today inflation.
    foreach ($p->cases()->get() as $c) { $c->save(); $casesTouched++; }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Suffrage records processed: ".$people->count().", flags fixed: {$flagsFixed}, cases recomputed: {$casesTouched}.\n";
echo "Done.\n";
'

echo
echo "Done."
