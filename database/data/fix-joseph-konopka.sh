#!/usr/bin/env bash
#
# Joseph D. Konopka -- corrected custody dates, birth date, and the second case.
#
# HE WAS ALREADY IN THE DATABASE, as /prisoner/joseph-konopka, with the custody
# start a year out.
#
# WHAT WAS WRONG
#   The case had arrest and incarceration on March 11, 2003 -- a day after the
#   sentencing, not the arrest. He was arrested on March 9, 2002 and was in
#   custody from that day. The release was recorded as July 28, 2019, a day
#   early. Between them the counter read 5,983 days, 16 years 4 months
#   17 days, understating the custody by just over a year.
#
#   Corrected to March 9, 2002 - July 29, 2019: 6,351 days, which is exactly
#   17 years, 4 months and 20 days.
#
# THE RECORD
#   Born                June 24, 1976
#   Arrested            March 9, 2002, in Chicago, found in the underground
#                       tunnels at the University of Illinois at Chicago. The
#                       investigation turned up sodium and potassium cyanide
#                       stored in a CTA substation and tied him to a series of
#                       Wisconsin arsons, computer intrusions and attacks on
#                       electrical, communications and air-navigation
#                       facilities.
#   Federal sentence    13 years for chemical-weapons possession, March 2003
#   Wisconsin sentence  20 years and 10 months, VACATED on appeal and replaced
#                       in November 2005 by seven years
#   Released            July 29, 2019
#
# THE WISCONSIN CASE CARRIES NO DATES, ON PURPOSE.
#   The profile page sums imprisoned_for_days across a prisoner’s cases. His
#   custody was continuous through both sentences, so putting the same span on
#   both rows would report roughly thirty-four years for a man who served
#   seventeen. The dates live on the federal chemical-weapons case; the
#   Wisconsin case records the charges, the vacated sentence and the November
#   2005 resentencing, and says in its own text that the custody is counted
#   once, next door.
#
# THE AKA IS REMOVED.
#   The record carried "Dr. Chaos" as an AKA, which showed as an italic line
#   under his name on the profile. It is cleared. The alias still appears in
#   the biography, where the sentence explains what it was, so nothing is lost
#   except the standalone byline. This runs whether the AKA currently reads
#   "Dr. Chaos" or the "Dr. Ch@os (Dr. Chaos)" form an earlier version of this
#   script wrote.
#
# NOT CHANGED
#   State stays Illinois -- that is where he was arrested and where the case
#   holding the custody dates was prosecuted, even though he was a Wisconsin
#   systems administrator and most of the underlying offences were in
#   Wisconsin. Ideologies stay as they are; the supplied record does not state
#   one and this script does not invent one.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-joseph-konopka.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$matches = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->where("slug", "joseph-konopka")
        ->orWhereRaw("LOWER(name) IN (?, ?)", ["joseph konopka", "joseph d. konopka"]))
    ->with("cases")
    ->get();

if ($matches->isEmpty()) { echo "NOT FOUND: Joseph Konopka\n"; exit(1); }
if ($matches->count() > 1) {
    echo "ABORT: {$matches->count()} records match. Refusing to guess:\n";
    foreach ($matches as $m) { echo "  {$m->slug}  {$m->name}\n"; }
    exit(1);
}

$p = $matches->first();

echo "BEFORE\n";
echo "  born=".($p->birthdate ? $p->birthdate->toDateString() : "-")."\n";
foreach ($p->cases as $c) {
    echo "  case  inc=".($c->incarceration_date ? $c->incarceration_date->toDateString() : "-")
        ."  rel=".($c->release_date ? $c->release_date->toDateString() : "-")
        ."  days=".($c->imprisoned_for_days ?? "null")."\n";
}

// ---- the man ---------------------------------------------------------------
$p->first_name = "Joseph";
$p->middle_name = "D.";
$p->last_name = "Konopka";
$p->aka = null;   // the Dr. Chaos AKA is removed; the alias stays in the biography
$p->gender = "Male";
$p->era = "2000s";
$p->affiliation = ["Realm of Ch@os"];
$p->setPartialDate("birthdate", 1976, 6, 24);
$p->in_custody = false;
$p->awaiting_trial = false;
$p->released = true;
$p->description = "Joseph D. Konopka, born June 24, 1976, was a Wisconsin computer systems administrator who used the alias Dr. Ch@os and led a loosely organized group called the Realm of Ch@os. He was arrested in Chicago on March 9, 2002 after being discovered in the underground tunnels at the University of Illinois at Chicago. The investigation uncovered sodium and potassium cyanide stored in a CTA substation and connected him to a series of Wisconsin arsons, computer intrusions and attacks on electrical, communications and air-navigation facilities. In March 2003 he received a 13-year federal sentence for chemical-weapons possession. A separate Wisconsin sentence of 20 years and 10 months was vacated on appeal and replaced in November 2005 by a seven-year sentence. He remained continuously in federal custody from March 9, 2002 until July 29, 2019 — 17 years, 4 months and 20 days.";
$p->save();

// ---- the federal chemical-weapons case: this row carries the custody -------
$federal = $p->cases->first(fn ($c) => stripos((string) $c->charges, "chemical weapon") !== false)
    ?? $p->cases->first()
    ?? $p->cases()->create([]);
$federal->charges = "Federal possession of a chemical weapon and related counts — sodium and potassium cyanide stored in a Chicago Transit Authority substation, found after his arrest in the underground tunnels at the University of Illinois at Chicago.";
$federal->plead = "Guilty";
$federal->convicted = "Yes — guilty plea.";
$federal->sentence = "Thirteen years (156 months) in federal prison, imposed in March 2003. He was in continuous federal custody from his arrest on March 9, 2002 until his release on July 29, 2019 — 17 years, 4 months and 20 days across both this sentence and the Wisconsin one. The whole span is recorded here, on one case, so it is counted once.";
$federal->setPartialDate("arrest_date", 2002, 3, 9);
$federal->setPartialDate("incarceration_date", 2002, 3, 9);
$federal->setPartialDate("sentenced_date", 2003, 3, 12);
$federal->setPartialDate("release_date", 2019, 7, 29);
$federal->save();

// ---- the Wisconsin case: charges and sentence only, no dates ---------------
$wisconsin = $p->cases()->where("charges", "like", "%Wisconsin%")->first()
    ?? $p->cases()->create([]);
$wisconsin->charges = "Wisconsin offences — a series of arsons, computer intrusions and attacks on electrical, communications and air-navigation facilities, carried out under the Realm of Ch@os banner.";
$wisconsin->convicted = "Yes. The original sentence of 20 years and 10 months was vacated on appeal and replaced in November 2005 by a seven-year sentence.";
$wisconsin->sentence = "Originally 20 years and 10 months, vacated on appeal, resentenced in November 2005 to seven years. NO CUSTODY DATES ARE RECORDED ON THIS CASE. His imprisonment ran continuously through both sentences, and the profile page sums the day counts across a prisoner’s cases, so repeating the span here would report roughly thirty-four years for a man who served seventeen. The dates are on the federal chemical-weapons case.";
$wisconsin->setPartialDate("sentenced_date", 2005, 11);
$wisconsin->setPartialDate("arrest_date", null);
$wisconsin->setPartialDate("incarceration_date", null);
$wisconsin->setPartialDate("release_date", null);
$wisconsin->save();

// ---- receipt ---------------------------------------------------------------
$p->refresh()->load("cases");
echo "\nAFTER\n";
echo "  {$p->name}  [{$p->slug}]   AKA ".($p->aka ?: "(removed)")."\n";
echo "  born ".$p->formatPartialDate("birthdate")."   age {$p->age}\n";
$total = 0;
foreach ($p->cases as $c) {
    $total += (int) $c->imprisoned_for_days;
    echo "  case  inc=".str_pad((string) ($c->formatPartialDate("incarceration_date") ?: "-"), 14)
        ." rel=".str_pad((string) ($c->formatPartialDate("release_date") ?: "-"), 14)
        ." sentenced=".str_pad((string) ($c->formatPartialDate("sentenced_date") ?: "-"), 14)
        ." days=".($c->imprisoned_for_days ?? "null")."\n";
}
echo "  counter: {$total} days (was 5,983)\n";
if ($total === 6351) {
    echo "  6,351 days = 17 years, 4 months, 20 days — matches the supplied figure exactly.\n";
} else {
    echo "  EXPECTED 6,351. Check the dates above.\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
