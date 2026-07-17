#!/usr/bin/env bash
#
# Merges the "Joseph Waller" duplicate into Omali Yeshitela (Waller is his
# birth name; the Yeshitela record already carries the photo and the real
# 2023 Tampa federal case). The Waller record's only case row is OCR bleed
# from the WWI register import and is dropped by the merge, not moved.
# Afterwards, dedupes the identical doubled case row on the Yeshitela record.
#
# Idempotent: re-running skips the already-merged group and finds no
# remaining duplicate case rows.
#
# Run from the repo root:  bash database/data/merge-waller-into-yeshitela.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoners:merge-duplicates --only=omali-yeshitela --apply

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "omali-yeshitela")->first();
if ($p) {
    $seen = [];
    foreach (\App\Models\PrisonerCase::where("prisoner_id", $p->id)->orderBy("created_at")->get() as $c) {
        $k = md5(json_encode([$c->charges, $c->sentence, $c->arrest_date, $c->incarceration_date, $c->release_date, $c->institution_id]));
        if (isset($seen[$k])) { $c->delete(); echo "Deleted duplicate case row {$c->id}\n"; }
        else { $seen[$k] = 1; }
    }
    echo "Cases remaining: " . \App\Models\PrisonerCase::where("prisoner_id", $p->id)->count() . "\n";
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
'

echo
echo "Done. Joseph Waller merged into Omali Yeshitela; duplicate case rows removed."
