#!/usr/bin/env bash
#
# Birth/death dates for the five Foley Square defense lawyers:
#   Abraham J. Isserman   born May 11, 1900     died April 22, 1988
#   George W. Crockett Jr. born August 10, 1909  died September 7, 1997
#   Richard Gladstein     born December 28, 1908 died May 16, 1981
#   Harry Sacher          born August 22, 1902   died May 22, 1963
#   Louis F. McCabe       born April 1896        died April 1964  (exact days unknown -> month precision)
#
# Idempotent. Run from the repo root:
#   bash database/data/foley-square-lawyer-dates.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
// [slug, birth [y,m,d|null], death [y,m,d|null]]
$data = [
    ["abraham-isserman",  [1900, 5, 11],  [1988, 4, 22]],
    ["george-crockett",   [1909, 8, 10],  [1997, 9, 7]],
    ["richard-gladstein", [1908, 12, 28], [1981, 5, 16]],
    ["harry-sacher",      [1902, 8, 22],  [1963, 5, 22]],
    ["louis-mccabe",      [1896, 4, null], [1964, 4, null]],
];
$done = 0;
foreach ($data as $d) {
    [$slug, $b, $x] = $d;
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "  not found: {$slug}\n"; continue; }
    $p->setPartialDate("birthdate", $b[0], $b[1] ?? null, $b[2] ?? null);
    $p->setPartialDate("death_date", $x[0], $x[1] ?? null, $x[2] ?? null);
    $p->save();
    echo "  {$slug}: born {$p->birthdate}, died {$p->death_date}\n"; $done++;
}
echo "\nSet dates on {$done} record(s).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Foley Square lawyer birth/death dates set."
