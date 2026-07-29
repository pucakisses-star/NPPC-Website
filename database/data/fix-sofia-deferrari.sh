#!/usr/bin/env bash
#
# Sofia DeFerrari -- the future release date, the missing custody dates,
# the empty case row, and her position at the top of the database.
#
# She held SORT_ORDER 1 -- the most recent case in the entire archive --
# because her release_date was December 22, 2028, a date that has not
# happened. It is her SCHEDULED release, and the newest-first ordering
# read the projection as a real event. She was also the only
# future-dated record among all 8,300+. Meanwhile the record had no
# arrest or incarceration date at all, so a woman sitting in Coffee
# Creek today showed an imprisonment counter of zero, and a second,
# completely empty case row hung off the record doing nothing.
#
# WHAT GOES WHERE:
#
#   Nov  4, 2020   arrested in downtown Portland after the post-election
#                  riot that defied a multi-department police operation
#                  with the Oregon National Guard deployed -- documented
#                  by her support committee, which names the co-arrestee
#   Jun 22, 2021   custody credit begins -- RECONSTRUCTED, and flagged
#                  as such on the case: Measure 11 sentences under ORS
#                  137.700 run day for day with no earned-time
#                  reduction, so the documented hard release of
#                  December 22, 2028 minus the 90-month term fixes the
#                  start exactly (the Matthey method: a documented
#                  expiry and a determinate term give the entry). Her
#                  bail status between the arrest and this date is not
#                  documented, so no custody is claimed for that gap.
#   (running)      she is IN CUSTODY at Coffee Creek, so the counter
#                  counts from June 22, 2021 to the present -- about
#                  1,863 days and climbing, instead of zero
#   Dec 22, 2028   the hard release date -- IN THE SENTENCE TEXT ONLY,
#                  the Lartigue rule: a projection is not an event, and
#                  this record is the proof, having sat above every
#                  actual 2026 case because of it
#
# THE EMPTY CASE ROW IS DELETED. Both matters -- the riot plea and the
# Beaverton robbery -- run concurrently and are one continuous custody,
# so they live on ONE case row; a second row would double-count days.
#
# HER SORT_ORDER IS SET TO 0 so the placement command can put her where
# her real dates belong -- with the 2020 arrests, not above the 2026
# cases. Run prisoners:place-zero-sort-by-year --apply afterwards
# (run-pending.sh does this automatically).
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-sofia-deferrari.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "sofia-deferrari")->with("cases")->first();
if (! $p) {
    echo "NOT FOUND: sofia-deferrari\n";
    exit(1);
}

$p->in_custody = true;
$p->released = false;
$p->awaiting_trial = false;
$p->description = "Sofia DeFerrari, known in the movement as Candle, is a non-binary trans woman and Egoist Anarchist in the custody of the Oregon Department of Corrections at Coffee Creek Correctional Facility in Wilsonville. Raised in Broward County, Florida and half-Venezuelan, she has been on hormone replacement therapy since March 2018 and uses she/they pronouns. She was arrested in downtown Portland on November 4, 2020, after a post-election riot that defied a multi-department police operation with the Oregon National Guard deployed — led, she has said, into a honeypot by an FBI informant inside her affinity group, whose legal name she later disclosed publicly as a deliberate demonstration against informing. She took public responsibility for the property destruction — windows of a shopping district, a church, and an ATM — and pleaded to Riot and Criminal Mischief in the First Degree with roughly 11,000 dollars in restitution. Most of her time, though, comes from a separate conviction for the armed robbery of a Beaverton 7-Eleven, which netted about 860 dollars and some cigars: she accepted the 90-month mandatory minimum under Oregon Measure 11, ORS 137.700, which runs day for day with no earned-time reduction. All sentences run concurrently, with a hard release date of December 22, 2028. In prison she changed her legal name from Sofia Johnson to Sofia de Ferrari, completed coursework toward a bachelor degree, and is supported by the June 11 solidarity network and the International Anarchist Defence Fund.";
$p->sort_order = 0;
$p->save();

$coffee = Institution::firstOrCreate(
    ["name" => "Coffee Creek Correctional Facility"],
    ["city" => "Wilsonville", "state" => "Oregon"],
);

// One continuous custody, one row: the concurrent matters share it, and
// a second row would double-count days. Keep the row that has content,
// delete the empty one.
$cases = $p->cases->sortByDesc(fn ($c) => mb_strlen((string) $c->charges.(string) $c->sentence));
$case = $cases->first() ?? $p->cases()->make([]);
$deleted = 0;
foreach ($cases->slice(1) as $extra) {
    $extra->delete();
    $deleted++;
}

$case->prisoner_id = $p->id;
$case->institution_id = $coffee->id;
$case->charges = "Riot and Criminal Mischief in the First Degree (the November 4, 2020 Portland riot), and Robbery in the First Degree (the armed robbery of a Beaverton 7-Eleven), all resolved by plea and running concurrently.";
$case->convicted = "Yes — by plea. Riot and Criminal Mischief in the First Degree on the Portland matter, with about 11,000 dollars in restitution; the 90-month mandatory minimum under Oregon Measure 11 (ORS 137.700) on the Beaverton robbery.";
$case->sentence = "Ninety months, the Measure 11 mandatory minimum, all counts concurrent, with a HARD RELEASE DATE OF DECEMBER 22, 2028 — a projection, which is why it lives here in the text and not in the release field: this record previously carried it as its release date and, the list running newest-first, sat at sort position 1 above every actual current case in the archive. The incarceration date of June 22, 2021 is RECONSTRUCTED: Measure 11 time runs day for day with no earned-time reduction, so the documented hard release minus the ninety-month term fixes the start of custody credit exactly. She was arrested November 4, 2020; whether and how long she was out between arrest and the start of the term is not documented, so no custody is claimed for that gap. She remains in custody at Coffee Creek, and the counter runs to the present.";
$case->setPartialDate("arrest_date", 2020, 11, 4);
$case->setPartialDate("incarceration_date", 2021, 6, 22);
$case->release_date = null;
$case->save();

$p->refresh()->load("cases");
$c = $p->cases->first();
echo "Sofia DeFerrari  [{$p->slug}]\n";
echo "  cases: ".$p->cases->count()."  (expect 1; {$deleted} empty row(s) deleted)\n";
echo "  arrest       ".(optional($c->arrest_date)->toDateString() ?: "-")."\n";
echo "  incarcerated ".(optional($c->incarceration_date)->toDateString() ?: "-")."   (reconstructed: 2028-12-22 minus 90 months)\n";
echo "  release      ".($c->release_date ? optional($c->release_date)->toDateString() : "(in custody — projection stays in the text)")."\n";
echo "  imprisoned_for_days = ".($c->imprisoned_for_days ?? "null")."  (counts to today; was 0)\n";
echo "  sort_order   ".$p->sort_order."   (0 — run prisoners:place-zero-sort-by-year --apply to slot her by her 2020 arrest)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
