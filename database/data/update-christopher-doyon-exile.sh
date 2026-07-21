#!/usr/bin/env bash
#
# Add the missing exile period to Christopher Doyon's ("Commander X") case.
# His stored case already has the indictment, guilty plea, 2022 conviction
# and sentence at Santa Rita Jail — but nothing on the ~decade he spent as
# a fugitive in exile before his 2021 arrest. The cases export records it:
#   - In exile since: 2011-01-29
#   - End of exile:   2021-06-11 (his arrest)
#   - ~3,786 days in exile
#
# Idempotent: only sets the exile fields if they are still empty.
# Run from the repo root:
#   bash database/data/update-christopher-doyon-exile.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "christopher-doyon")->first();
if (! $p) { echo "christopher-doyon not found\n"; return; }

$case = $p->cases()->first();
if (! $case) { echo "no case row to enrich\n"; return; }

$changed = false;

if (empty($case->in_exile_since)) {
    $case->in_exile_since = "2011-01-29";
    $changed = true; echo "SET in_exile_since 2011-01-29\n";
}
if (empty($case->end_of_exile)) {
    $case->end_of_exile = "2021-06-11";
    $changed = true; echo "SET end_of_exile 2021-06-11\n";
}
if (empty($case->in_exile_for_days)) {
    $case->in_exile_for_days = 3786;
    $changed = true; echo "SET in_exile_for_days 3786\n";
}

if ($case->isDirty()) { $case->save(); }

echo $changed ? "Updated christopher-doyon case.\n" : "Nothing to do.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Christopher Doyon exile period added."
