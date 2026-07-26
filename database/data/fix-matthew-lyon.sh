#!/usr/bin/env bash
#
# Matthew Lyon -- verified Sedition Act incarceration record.
#
# - Birth July 14, 1749; death August 1, 1822.
# - Main case: convicted October 9, 1798 (Justice William Paterson presiding);
#   jailed at Vergennes, Vermont through February 9, 1799 (four calendar
#   months; documented congressional calculation 123 days; 1,000 dollar fine
#   plus 60.96 dollars costs paid at release).
# - Adds the brief pretrial detention: arrested October 6, 1798 in Fair
#   Haven, Vermont; released on bail October 7, 1798.
# - The November 7, 1799 second warrant was never served (no second
#   incarceration) -- noted in the bio only.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-matthew-lyon.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "matthew-lyon")->first()
    ?? Prisoner::withoutGlobalScopes()->where("name", "like", "%Matthew Lyon%")->first();
if (! $p) { echo "NOT FOUND: Matthew Lyon\n"; exit(1); }

$p->setPartialDate("birthdate", 1749, 7, 14);
$p->setPartialDate("death_date", 1822, 8, 1);
$p->in_custody = false;
$p->awaiting_trial = false;
$p->released = true;
$p->description = "Matthew Lyon, a Congressman from Vermont, was convicted under the Sedition Act for publishing and circulating criticism of President John Adams and his administration, including language accusing the administration of pursuing power, pomp, adulation and selfish avarice. He argued that the Sedition Act was unconstitutional, but Justice William Paterson instructed the jury not to consider that question, and the jury convicted him after about one hour. Indicted October 5, 1798 and arrested the next day in Fair Haven, Vermont, he was held until posting bail on October 7, then convicted on October 9 and sent under guard to the jail at Vergennes for four calendar months. He was released February 9, 1799 after paying a 1,000 dollar fine and 60.96 dollars in court costs; a congressional report records 123 days during which imprisonment prevented him from attending Congress. While imprisoned he successfully campaigned for reelection to Congress and left for Philadelphia immediately after his release. A second federal arrest warrant was issued November 7, 1799 over his published criticism of his trial and imprisonment, but the deputy marshal reported in April 1800 that he could not find Lyon in Vermont, so no second incarceration is confirmed.";
$p->save();
echo "{$p->name}: 1749-07-14 - 1822-08-01, bio updated.\n";

$inst = Institution::firstOrCreate(["name" => "Vergennes Jail"], ["city" => "Vergennes", "state" => "Vermont"]);

// Main sentence: October 9, 1798 - February 9, 1799.
$main = $p->cases()->where("charges", "not like", "%retrial%")->orderBy("created_at")->first();
if (! $main) { $main = $p->cases()->make(); $main->prisoner_id = $p->id; }
if (! $main->charges) { $main->charges = "Sedition Act -- publishing and circulating criticism of President John Adams and his administration."; }
$main->institution_id = $inst->id;
$main->judge = "William Paterson";
$main->convicted = "Yes — convicted October 9, 1798 under the Sedition Act";
$main->sentence = "Four calendar months at the jail in Vergennes, Vermont, plus a 1,000 dollar fine and 60.96 dollars in court costs; the commitment warrant authorized continued detention until the fine and costs were paid. A congressional report documents 123 days of imprisonment.";
$main->setPartialDate("arrest_date", 1798, 10, 6);
$main->setPartialDate("incarceration_date", 1798, 10, 9);
$main->setPartialDate("release_date", 1799, 2, 9);
$main->save();
echo "  main case: 1798-10-09 -> 1799-02-09, days={$main->imprisoned_for_days}.\n";

// Brief pretrial detention: October 6-7, 1798.
$pre = $p->cases()->where("charges", "like", "%retrial%")->first();
if (! $pre) {
    $pre = $p->cases()->create([
        "charges" => "Pretrial detention on the Sedition Act indictment -- arrested by a deputy U.S. marshal in Fair Haven, Vermont.",
    ]);
}
$pre->convicted = "Released on bail October 7, 1798 after pleading not guilty";
$pre->setPartialDate("arrest_date", 1798, 10, 6);
$pre->setPartialDate("incarceration_date", 1798, 10, 6);
$pre->setPartialDate("release_date", 1798, 10, 7);
$pre->save();
echo "  pretrial case: 1798-10-06 -> 1798-10-07, days={$pre->imprisoned_for_days}.\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
