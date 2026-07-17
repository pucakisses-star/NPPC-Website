#!/usr/bin/env bash
#
# Fixes the findings of the July 2026 duplicate-photo audit (all 1,462 photo
# files were content-hashed; six pairs of records shared identical bytes):
#
#  1. Three pairs were duplicate RECORDS of the same person — merged via
#     prisoners:merge-duplicates (descriptions preserved, names kept as
#     aliases): George Andreychine -> George Andreytchine,
#     Martin Luther King -> Martin Luther King Jr., JoAnne Little -> Joan
#     Little.
#  2. Two records wore a DIFFERENT person's portrait — cleared: James Larson
#     (a WWI Leavenworth brickmaker) had Irish labor leader James Larkin's
#     portrait; John I. Turner (an IWW lumberjack) had the British anarchist
#     John Turner's portrait.
#  3. Two shared images are intentional and left alone: the Soto couple photo
#     (documented in SetPrairielandPhotos — the support committee publishes a
#     single couple photo) and the Carberry/Smith NYPD surveillance still
#     (both co-defendants appear in the frame).
#
# Idempotent: merged groups are skipped on re-run and the photo clears
# no-op once empty.
#
# Run from the repo root:  bash database/data/fix-duplicate-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoners:merge-duplicates --only=george-andreytchine,martin-luther-king-jr,joan-little --apply

php artisan tinker --execute='
$cleared = 0;
foreach (["james-larson", "john-i-turner"] as $slug) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "MISS {$slug}\n"; continue; }
    if (empty($p->photo)) { echo "SKIP {$slug} (no photo)\n"; continue; }
    echo "CLEAR {$slug} (was {$p->photo})\n";
    $p->photo = null;
    $p->save();
    $cleared++;
}
if ($cleared > 0) {
    \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
}
echo "Done. {$cleared} wrong photo(s) cleared.\n";
'

echo
echo "Done. Duplicate-photo audit fixes applied."
