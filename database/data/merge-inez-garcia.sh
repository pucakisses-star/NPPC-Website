#!/usr/bin/env bash
#
# Merges the duplicate Inez García records surfaced while auditing the
# Freedom Archives scan of Breakthrough vol. 1 no. 3-4 (Oct-Dec 1977) —
# the issue itself was already in the archive as
# pfoc-breakthrough-3-october-december-1977 (a different digitization of
# the same 84 pages), and every prisoner it covers (Skyhorse, Mohawk,
# Dessie Woods, Cheryl Todd, Wanrow, Little, Drumgo, Stanford, Assata)
# was already present. The one finding: inez-garcia-2, a caseless stub
# of the same 1974 Soledad self-defense defendant, folds into the
# accented canonical record.
#
# Run from the repo root:  bash database/data/merge-inez-garcia.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoners:merge-duplicates --only=inez-garcia --apply

php artisan tinker --execute='
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Inez Garcia merge applied."
