#!/usr/bin/env bash
#
# Remove Thomas Garrett -- the Quaker Underground Railroad stationmaster of
# Wilmington, Delaware, tried with John Hunn at the New Castle Court House in
# 1848 for violating the fugitive-slave laws.
#
# The record carried an 1848 incarceration date, but its own sentence text
# recorded the penalty as a $1,500 fine that nearly bankrupted him. Fines are
# not custody, and no imprisonment is documented in that case.
#
# The roster entry has also been deleted from
# prisoners:add-underground-railroad-prisoners, which creates-or-updates by
# name and would otherwise put him straight back on the next run.
#
# The record is printed in full first, cases and dates included, so the output
# is a receipt of exactly what was removed. Aborts rather than guessing if more
# than one record matches.
#
# Deletes by default. REVIEW=1 hides it instead (under_review = true), which
# keeps the data and is reversible from the admin.
#
#   bash database/data/remove-thomas-garrett.sh
#   REVIEW=1 bash database/data/remove-thomas-garrett.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

REVIEW="${REVIEW:-0}" php artisan tinker --execute='
use App\Models\Prisoner;

$review = getenv("REVIEW") === "1";

$matches = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->where("slug", "thomas-garrett")
        ->orWhereRaw("LOWER(name) = ?", ["thomas garrett"]))
    ->with("cases.institution")
    ->get();

if ($matches->isEmpty()) { echo "Not found: Thomas Garrett (already removed?)\n"; exit(0); }
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
    echo "  case inc=".($c->incarceration_date ? $c->incarceration_date->toDateString() : "-")
        ." rel=".($c->release_date ? $c->release_date->toDateString() : "-")
        ." days=".($c->imprisoned_for_days ?? "null")."\n";
    echo "       sentence: ".substr((string) $c->sentence, 0, 90)."\n";
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

echo "\nWorth a look for the same problem -- a fine or a voided conviction recorded\n";
echo "with an incarceration date, and no documented jail time:\n";
foreach (["John Hunn", "Luther Donnell"] as $name) {
    $q = Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", [strtolower($name)])->with("cases")->first();
    if (! $q) { continue; }
    foreach ($q->cases as $c) {
        echo "  ".str_pad($q->name, 18)." inc=".($c->incarceration_date ? $c->incarceration_date->toDateString() : "-")
            ."  ".substr((string) $c->sentence, 0, 70)."\n";
    }
}
echo "Neither is touched here -- say the word and they can go the same way.\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
