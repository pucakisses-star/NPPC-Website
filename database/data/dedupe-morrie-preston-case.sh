#!/usr/bin/env bash
#
# Morrie R. Preston has the SAME Goldfield murder case attached twice, and the
# page sums imprisoned_for_days across cases, so his counter reads about
# 14 years 2 months instead of the ~7 years 2 months he actually served:
#
#   detailed case  1907-05-09 -> 1914-07-09 = 2618 days  (Nevada State Prison)
#   sparse copy    1907-05-09 -> 1914       = 2429 days  (no institution)
#                                             ------------
#                                             5047 days
#
# This keeps the richer case (scored by how many fields are populated) and
# deletes the other copies, folding any field the survivor is missing across
# first so no detail is lost.
#
# Preview by default; set APPLY=1 to write:
#   bash database/data/dedupe-morrie-preston-case.sh
#   APPLY=1 bash database/data/dedupe-morrie-preston-case.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

APPLY="${APPLY:-0}" php artisan tinker --execute='
use App\Models\Prisoner;

$apply = getenv("APPLY") === "1";

$p = Prisoner::withoutGlobalScopes()->where("slug", "morrie-r-preston")->first()
    ?? Prisoner::withoutGlobalScopes()->where("name", "like", "%Morrie%Preston%")->first();
if (! $p) { echo "NOT FOUND: Morrie R. Preston\n"; exit(1); }

$cases = $p->cases()->orderBy("created_at")->get();
echo "{$p->name}: ".$cases->count()." case(s)\n\n";

$fields = ["charges", "arrest_date", "indicted", "sentenced_date", "incarceration_date", "release_date",
           "convicted", "plead", "prosecutor", "judge", "sentence", "institution_id"];

$score = function ($c) use ($fields) {
    $n = 0;
    foreach ($fields as $f) { if (! empty($c->{$f})) { $n++; } }

    return $n;
};

foreach ($cases as $c) {
    echo "  case {$c->id}\n";
    echo "    filled fields: ".$score($c)."/".count($fields)."  days=".($c->imprisoned_for_days ?? "null")."\n";
    echo "    inc=".($c->incarceration_date ?: "-")." rel=".($c->release_date ?: "-")." inst=".($c->institution_id ? "yes" : "no")."\n";
}

if ($cases->count() < 2) { echo "\nNothing to de-duplicate.\n"; exit(0); }

$sorted = $cases->sortByDesc($score)->values();
$keepCase = $sorted->first();
$drop = $sorted->slice(1);

echo "\nkeep:  {$keepCase->id} (".$score($keepCase)." fields, days=".($keepCase->imprisoned_for_days ?? "null").")\n";
foreach ($drop as $d) { echo "drop:  {$d->id} (".$score($d)." fields, days=".($d->imprisoned_for_days ?? "null").")\n"; }

if (! $apply) {
    echo "\nPREVIEW ONLY -- nothing written. Re-run with APPLY=1 to apply:\n";
    echo "  APPLY=1 bash database/data/dedupe-morrie-preston-case.sh\n";
    exit(0);
}

// Fold anything the survivor lacks across from the copies being removed.
foreach ($drop as $d) {
    foreach ($fields as $f) {
        if (empty($keepCase->{$f}) && ! empty($d->{$f})) {
            $keepCase->{$f} = $d->{$f};
            echo "  {$f} folded in from {$d->id}\n";
        }
    }
}
$keepCase->save();

foreach ($drop as $d) { $d->delete(); echo "  deleted case {$d->id}\n"; }

$p->refresh();
$total = $p->cases->sum("imprisoned_for_days");
echo "\nresult: ".$p->cases->count()." case remaining, total days={$total} (expected 2618)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
