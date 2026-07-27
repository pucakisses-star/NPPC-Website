#!/usr/bin/env bash
#
# Joseph William Smith -- corrected record. The "Imprisoned For 119 years"
# counter came from a missing release date: with no release on file and a
# stale in_custody flag, the duration was being counted up to today.
#
#   Silva shooting            March 10, 1907
#   Arrested                  March 12, 1907   (custody starts here)
#   Convicted of manslaughter May 9, 1907
#   Sentence                  10 years
#   Released on parole        November 14, 1911 (1,708 days = 4y 8m 2d)
#   Posthumously pardoned     May 12, 1987     (recorded in the case text)
#
#   Born  March 1, 1870
#   Died  1935, Oakland, California (year precision)
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-joseph-william-smith.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "joseph-william-smith")->first()
    ?? Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) LIKE ?", ["%joseph%smith%"])->first();
if (! $p) { echo "NOT FOUND: Joseph William Smith\n"; exit(1); }

$p->first_name = "Joseph";
$p->middle_name = "William";
$p->last_name = "Smith";
$p->setPartialDate("birthdate", 1870, 3, 1);
$p->setPartialDate("death_date", 1935, null, null);

// Clear the flags that were inflating the counter.
$p->in_custody = false;
$p->awaiting_trial = false;
$p->released = true;
$p->save();
echo "{$p->name}: born 1870-03-01, died 1935 (year precision), age {$p->age}.\n";

$c = $p->cases()->orderBy("created_at")->first();
if (! $c) { $c = $p->cases()->make(); $c->prisoner_id = $p->id; }
if (! $c->charges) {
    $c->charges = "Manslaughter, for the shooting of Silva on March 10, 1907.";
}
$c->convicted = "Yes — convicted of manslaughter May 9, 1907; posthumously pardoned May 12, 1987";
$c->sentence = "Ten years. Held from his arrest on March 12, 1907 until release on parole on November 14, 1911 -- 4 years, 8 months and 2 days. Posthumously pardoned on May 12, 1987.";
$c->setPartialDate("arrest_date", 1907, 3, 12);
$c->setPartialDate("sentenced_date", 1907, 5, 9);
$c->setPartialDate("incarceration_date", 1907, 3, 12);
$c->setPartialDate("release_date", 1911, 11, 14);
$c->save();
echo "  case: 1907-03-12 -> 1911-11-14, days={$c->imprisoned_for_days} (expected 1708).\n";

// Any other cases on this record that are open-ended would keep inflating the
// counter, so report them rather than leaving a silent surprise.
foreach ($p->cases()->get() as $other) {
    if ($other->id !== $c->id && $other->incarceration_date && ! $other->release_date) {
        echo "  NOTE: another case (".$other->id.") has no release date, days=".($other->imprisoned_for_days ?? "null")."\n";
    }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
