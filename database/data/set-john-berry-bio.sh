#!/usr/bin/env bash
#
# Set John Berry's biography to the site owner's provided text, and align his
# exile-end year to 1964 (the bio states he returned to the U.S. in 1964, so
# end_of_exile is updated from 1963 to 1964 to keep the record consistent).
#
# Idempotent. Run from the repo root:
#   bash database/data/set-john-berry-bio.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "john-berry")->first();
if (! $p) { echo "john-berry not found.\n"; return; }

$p->description = trim(file_get_contents(base_path("database/data/john-berry-bio.txt")));
$p->save();

$c = $p->cases()->first();
if ($c) {
    $c->setPartialDate("end_of_exile", 1964);
    $c->save();
    echo "end_of_exile set to 1964 (in_exile_for_days={$c->in_exile_for_days}).\n";
}

$p->refresh();
echo "john-berry bio set (".strlen($p->description)." chars).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. John Berry bio updated."
