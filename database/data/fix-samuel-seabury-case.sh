#!/usr/bin/env bash
#
# Samuel Seabury: 1775 detention record and bio.
#
# Arrested at the grammar school he ran in Westchester County, New York on
# November 22, 1775 by armed militiamen, taken to Connecticut and held at New
# Haven without charge; released December 23, 1775 after petitioning the
# Connecticut General Assembly. Sets the case dates (arrest, incarceration,
# release), records that he was never charged, and applies the bio.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-samuel-seabury-case.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "samuel-seabury")->first()
    ?? Prisoner::withoutGlobalScopes()->where("name", "like", "%Samuel Seabury%")->first();
if (! $p) { echo "NOT FOUND: Samuel Seabury\n"; exit(1); }

$p->description = "Samuel Seabury was an Anglican clergyman in Westchester County, New York, and prominent political organizer who published numerous writings criticizing the early American Congress. In November 1775 he was arrested at a Grammar School he ran by armed militiamen and brought to Connecticut, where he was held prisoner in New Haven for weeks without being charged with any crime. He was eventually released after petitioning the Connecticut General Assembly for his freedom. He later became the first bishop of the Episcopal Church in the United States.";
$p->in_custody = false;
$p->awaiting_trial = false;
$p->released = true;
$p->save();
echo "{$p->name}: bio updated.\n";

$inst = Institution::firstOrCreate(["name" => "New Haven Jail"], ["city" => "New Haven", "state" => "Connecticut"]);

$c = $p->cases()->orderBy("created_at")->first();
if (! $c) { $c = $p->cases()->make(); $c->prisoner_id = $p->id; }
if (! $c->charges) {
    $c->charges = "Held without charge after being seized by armed militiamen at the grammar school he ran in Westchester County, New York, for his writings criticizing the Continental Congress.";
}
$c->institution_id = $inst->id;
$c->convicted = "Never charged — released after petitioning the Connecticut General Assembly";
if (! $c->sentence) {
    $c->sentence = "No sentence — held at New Haven, Connecticut from November 22 to December 23, 1775 without being charged with any crime.";
}
$c->setPartialDate("arrest_date", 1775, 11, 22);
$c->setPartialDate("incarceration_date", 1775, 11, 22);
$c->setPartialDate("release_date", 1775, 12, 23);
$c->save();

echo "  case: 1775-11-22 -> 1775-12-23, days={$c->imprisoned_for_days}, institution={$inst->name}.\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
