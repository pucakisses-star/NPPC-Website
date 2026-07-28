#!/usr/bin/env bash
#
# Andrew Lawrence -- corrected incarceration record.
#
# The record had only an arrest date (April 3, 1988) and a bare "4 months"
# sentence, so the counter showed nothing and the story read as though he
# was released after the action. He was not: he and two of the other
# Nuclear Navy Plowshares defendants refused release on personal
# recognizance -- Gregory Boertje was held on \$25,000 bond over an earlier
# Plowshares matter, and Lawrence, Philip Berrigan and Sister Margaret
# McKenna would not leave without him -- so Lawrence stayed jailed from
# arrest through trial and sentencing.
#
# Corrected chronology:
#
#   Apr  3, 1988  arrested aboard the USS Iowa, Norfolk Naval Station;
#                 custody continuous from this day
#   Apr  4, 1988  federal magistrate appearance; charged with criminal
#                 trespass; refused personal-recognizance release
#   May 19, 1988  convicted at a bench trial before U.S. Magistrate
#                 Tommy Miller (a later chronology misplaces the
#                 proceedings in 1989; contemporary reporting says 1988)
#   Jul 21, 1988  sentenced to four months -- 109 days already served
#   after that    transferred to a Philadelphia jail for a separate
#                 courthouse-roof banner charge
#   release       UNKNOWN -- so no release date is set and the counter
#                 stays empty rather than showing a fabricated span.
#                 The 109-day confirmed minimum lives in the sentence
#                 text. The four-month Norfolk sentence would have run
#                 out around August 3, 1988, but he had been moved into
#                 Philadelphia custody by then, so that is not a release
#                 date.
#
# A SECOND CASE is added for the Philadelphia matter -- the banner hung
# from the federal courthouse roof during one of the Epiphany Plowshares
# trials. Neither the precise charge, the disposition, nor any custody
# dates have been recovered, so the case carries text only and no dates:
# it must not add anything to the counter (the Konopka rule).
#
# BIRTHDATE: about 1959-60, known only from his age (28) in an April 1988
# profile. A two-year window does not fit a year-precision field, so the
# birthdate stays unset and the bio carries his age (the Barbara Katt
# rule). State is set to Maryland (he lived at Jonah House in Baltimore).
#
# The Norfolk institution row ("Norfolk Naval Station (USS Iowa) -- Nuclear
# Navy Plowshares") is the action-site convention shared with Margaret
# McKenna and is left as it is.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-andrew-lawrence.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "andrew-lawrence")->with("cases")->first();
if (! $p) {
    echo "NOT FOUND: andrew-lawrence\n";
    exit(1);
}

$p->state = "Maryland";
$p->in_custody = false;
$p->released = true;
$p->awaiting_trial = false;
$p->description = "Andrew Lawrence was a 28-year-old former Peace Corps volunteer and peace activist living at Jonah House in Baltimore, also described as associated with the Community for Creative Non-Violence. On Easter Sunday, April 3, 1988, he joined Philip Berrigan, Sister Margaret McKenna and Gregory Boertje in boarding the USS Iowa with a public tour at Norfolk Naval Station. Calling themselves the Nuclear Navy Plowshares, the four separated from the tour, used hammers and bolt cutters against two armored Tomahawk cruise-missile launcher boxes, poured blood on the equipment and displayed banners reading “Seek the Disarmed Christ” and “Tomahawks Into Plowshares,” stopping when naval security ordered them to. Lawrence was arrested and charged with federal criminal trespass. Although offered release on personal recognizance, he refused and remained jailed in solidarity with Boertje, whose bond had been set at \$25,000 over an earlier Plowshares prosecution. Tried without a jury before U.S. Magistrate Tommy Miller, Lawrence admitted the action but argued it was justified by international law and the threat posed by nuclear weapons; he was convicted on May 19, 1988 and sentenced on July 21, 1988 to four months in prison, having by then already spent 109 days in custody. He was then transferred to a Philadelphia jail to face a separate charge connected with hanging a banner from the federal courthouse roof during one of the Epiphany Plowshares trials; his final release date has not been recovered.";
$p->save();

// ---- the Norfolk case ------------------------------------------------------
$norfolk = $p->cases->first(fn ($c) => stripos((string) $c->charges, "trespass") !== false)
    ?? $p->cases->first()
    ?? $p->cases()->create([]);
$norfolk->charges = "Federal criminal trespass — the Easter Sunday 1988 Nuclear Navy Plowshares action aboard the USS Iowa at Norfolk Naval Station, in which the four hammered and poured blood on two armored Tomahawk cruise-missile launcher boxes and displayed anti-nuclear banners.";
$norfolk->plead = "Not guilty — admitted the action, arguing it was justified by international law and the threat posed by nuclear weapons";
$norfolk->convicted = "Yes — convicted May 19, 1988 at a bench trial (a later Plowshares chronology misplaces the proceedings in 1989; contemporary reporting establishes 1988)";
$norfolk->judge = "U.S. Magistrate Tommy Miller";
$norfolk->sentence = "Four months’ imprisonment, imposed July 21, 1988. Offered release on personal recognizance the day after the action, he refused so long as codefendant Gregory Boertje was held on \$25,000 bond, and stayed jailed from arrest through trial and sentencing — 109 days, three months and eighteen days, already served by the sentencing date. After sentencing he was transferred to Philadelphia custody for a separate charge, so his release date is unknown and the counter is left empty; the four-month term would otherwise have expired around August 3, 1988.";
$norfolk->setPartialDate("arrest_date", 1988, 4, 3);
$norfolk->setPartialDate("incarceration_date", 1988, 4, 3);
$norfolk->release_date = null;
$norfolk->save();

// ---- the Philadelphia case (text only, no dates -- disposition unknown) ----
$philly = $p->cases->first(fn ($c) => stripos((string) $c->charges, "Philadelphia") !== false);
if (! $philly) {
    $philly = $p->cases()->create([]);
}
$philly->charges = "Philadelphia — a separate charge arising from the hanging of a banner from the federal courthouse roof during one of the Epiphany Plowshares trials. He was transferred to a Philadelphia jail after his July 21, 1988 Norfolk sentencing. The precise offense, the disposition, any additional sentence and his ultimate release date have not been recovered, so this case carries no dates and adds nothing to the counter.";
$philly->arrest_date = null;
$philly->incarceration_date = null;
$philly->release_date = null;
$philly->save();

$p->refresh()->load("cases");
echo "Andrew Lawrence  [{$p->slug}]  state={$p->state}\n";
foreach ($p->cases as $c) {
    echo "  case  arr=".($c->arrest_date ? $c->arrest_date->toDateString() : "-")
        ."  inc=".($c->incarceration_date ? $c->incarceration_date->toDateString() : "-")
        ."  rel=".($c->release_date ? $c->release_date->toDateString() : "-")
        ."  days=".($c->imprisoned_for_days ?? "null")
        ."  ".substr((string) $c->charges, 0, 40)."...\n";
}
echo "Counter stays empty by design: release date unknown (confirmed minimum 109 days is in the text).\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
