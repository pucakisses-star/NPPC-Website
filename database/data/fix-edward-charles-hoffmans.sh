#!/usr/bin/env bash
#
# Edward Charles Hoffmans -- corrected and expanded record.
#
# The record was a stub: "indicted on the four federal counts", one case with
# no dates, counter at zero. He was not merely indicted. He was committed to a
# federal medical prison, released on bond, pleaded guilty, drew three years,
# and served at FCI Sandstone.
#
# THE RECORD
#   May 25, 1969        with fourteen others, entered the Selective Service
#                       offices at 2355 West 63rd Street, Chicago before dawn,
#                       removed about 50 sacks holding roughly 40,000 draft
#                       records and burned them in the alley; the group stayed,
#                       singing, until arrested and taken to Cook County Jail.
#                       The date of his initial release is not found.
#   June 3, 1969        indicted with all fifteen on four counts: damage to
#                       federal property (18 U.S.C. 1361), destruction of
#                       records (2071), interference with the Selective
#                       Service Act, and conspiracy (371). The Seventh Circuit
#                       names him Edward Charles Hoffmans.
#   June 3, 1970        found incompetent by Judge Edwin Robson, severed from
#                       the main trial, committed to the federal medical
#                       center at Springfield, Missouri
#   August 21, 1970     found competent and freed on bond -- 79 days committed
#   November 18, 1970   pleaded guilty; three years, no probation
#   January 8, 1971     surrendered and entered federal custody; held at FCI
#                       Sandstone, Minnesota
#   after Feb 16, 1972  release date NOT FOUND. A March 10, 1972 article
#                       confirms he was STILL at Sandstone on February 16,
#                       1972 -- that date is sometimes misread as his release,
#                       and it is not.
#
# WHAT THE COUNTER WILL SHOW, AND WHY
#   The Springfield commitment has both ends documented, so it is entered as
#   its own case and counts: 79 days. The Sandstone sentence has a start
#   (January 8, 1971) and no documented end, and he is flagged released, so it
#   counts NOTHING -- the page will show 79 days against a documented minimum
#   of 483. That is the standing policy: a counter never runs on an invented
#   release date, and February 16, 1972 is a date he was still inside, not a
#   release. The at-least-404-days floor lives in the case text. Find the
#   parole notice or prison register and the counter completes itself.
#
# IDENTITY
#   Middle name Charles; the display name and slug stay as they are. The
#   surname genuinely ends in s.
#
#   Birth is NOT set: 31 on May 25, 1969 and 67 in a 2005 interview puts it in
#   1937 or 1938 -- two calendar years, so either would be a guess. It goes in
#   the bio instead.
#
#   Death IS set, at year precision, to 2019 -- with a caveat. What the source
#   establishes is that he had died BY 2019 (Access Living 2019 annual report,
#   donations in his memory), not that he died in it. Leaving the field empty
#   would show him as living, which is known false; 2019 is the latest year
#   consistent with the evidence and the bio says exactly what is known.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-edward-charles-hoffmans.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;

$matches = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->whereIn("slug", ["edward-hoffmans", "edward-charles-hoffmans", "ed-hoffmans"])
        ->orWhereRaw("LOWER(name) IN (?, ?, ?)", ["edward hoffmans", "edward charles hoffmans", "ed hoffmans"]))
    ->with("cases")
    ->get();

if ($matches->isEmpty()) { echo "NOT FOUND: Edward Hoffmans\n"; exit(1); }
if ($matches->count() > 1) {
    echo "ABORT: {$matches->count()} records match. Refusing to guess:\n";
    foreach ($matches as $m) { echo "  {$m->slug}  {$m->name}\n"; }
    exit(1);
}

$p = $matches->first();

echo "BEFORE\n";
foreach ($p->cases as $c) {
    echo "  case  arr=".($c->arrest_date ? $c->arrest_date->toDateString() : "-")
        ."  inc=".($c->incarceration_date ? $c->incarceration_date->toDateString() : "-")
        ."  rel=".($c->release_date ? $c->release_date->toDateString() : "-")
        ."  days=".($c->imprisoned_for_days ?? "null")."\n";
}

// ---- the man ---------------------------------------------------------------
$p->first_name = "Edward";
$p->middle_name = "Charles";
$p->last_name = "Hoffmans";
$p->aka = "Ed Hoffmans";
$p->gender = "Male";
$p->state = "Illinois";
$p->era = "1960s";
$p->setPartialDate("death_date", 2019);   // died BY 2019; see the header note
$p->in_custody = false;
$p->awaiting_trial = false;
$p->released = true;
$p->description = "Edward Charles Hoffmans — the surname really does end in s — was 31 at the time of the Chicago 15 action, living in Iowa City, working as a draft counselor after teaching English at the University of Northern Iowa, and already nationally controversial for publicly advocating draft resistance. Before dawn on May 25, 1969 he and fourteen others entered the Selective Service offices at 2355 West 63rd Street in Chicago, removed roughly fifty sacks containing about 40,000 draft records and burned them in the alley, then stayed at the scene singing until police arrested them and took them to Cook County Jail. A federal grand jury indicted all fifteen on June 3, 1969 on four counts: willful damage to federal property, destruction of federal records, interference with the Military Selective Service Act, and conspiracy. His case did not follow the ten defendants convicted by the jury in June 1970: on June 3, 1970 Judge Edwin Robson found him incompetent and severed his case, committing him to the federal medical center at Springfield, Missouri, where he was found competent shortly after arriving and released on bond on August 21 — a commitment of about 79 days. On November 18, 1970 he pleaded guilty under an agreement calling for three years in federal prison with no probation, surrendered on January 8, 1971, and served at the Federal Correctional Institution at Sandstone, Minnesota, where he was confirmed still imprisoned on February 16, 1972; the date of his actual release has not been found. His reported ages place his birth in 1937 or 1938. He later became a prominent Chicago disability-rights activist with ADAPT, and Access Living’s 2019 annual report lists donations in his memory, establishing that he had died by that year; no obituary or exact date of death has been located.";
$p->save();

// ---- case 1: the Chicago 15 prosecution and the Sandstone sentence ---------
$sandstone = Institution::firstOrCreate(
    ["name" => "FCI Sandstone"],
    ["city" => "Sandstone", "state" => "Minnesota"],
);

$main = $p->cases->first(fn ($c) => stripos((string) $c->charges, "draft-record") !== false
        || stripos((string) $c->charges, "Selective Service") !== false)
    ?? $p->cases->first()
    ?? $p->cases()->create([]);
$main->institution_id = $sandstone->id;
$main->charges = "Willful damage to federal property (18 U.S.C. § 1361), removal, mutilation and destruction of federal records (§ 2071), interference with the administration of the Military Selective Service Act, and conspiracy (§ 371) — the May 25, 1969 Chicago 15 draft-record burning at the Selective Service offices at 2355 West 63rd Street.";
$main->indicted = "June 3, 1969, with all fifteen defendants, by a federal grand jury.";
$main->plead = "Guilty — November 18, 1970, under an agreement calling for three years and no probation.";
$main->convicted = "Yes — by guilty plea on November 18, 1970, his case having been severed from the main trial on June 3, 1970 when Judge Edwin Robson found him incompetent.";
$main->judge = "Edwin A. Robson";
$main->sentence = "Three years in federal prison, no probation. Arrested at the scene on May 25, 1969 and taken to Cook County Jail; the date of his initial release is not found, so that first detention adds nothing to the counter. He surrendered on January 8, 1971 and served at FCI Sandstone, Minnesota, where a March 1972 report confirms he was still imprisoned on February 16, 1972 — a date sometimes misread as his release, which it is not. His actual release date has not been found, so NO release is recorded and this case adds nothing to the counter despite a documented minimum of 404 days served; the full term would nominally have run into January 1974, with parole and good time likely producing an earlier release.";
$main->setPartialDate("arrest_date", 1969, 5, 25);
$main->setPartialDate("sentenced_date", 1970, 11, 18);
$main->setPartialDate("incarceration_date", 1971, 1, 8);
$main->setPartialDate("release_date", null);
$main->save();

// ---- case 2: the Springfield medical commitment, both ends documented ------
$springfield = Institution::firstOrCreate(
    ["name" => "United States Medical Center for Federal Prisoners, Springfield"],
    ["city" => "Springfield", "state" => "Missouri"],
);

$medical = $p->cases()->where("charges", "like", "%medical%")->first()
    ?? $p->cases()->create([]);
$medical->institution_id = $springfield->id;
$medical->charges = "Federal medical commitment — on June 3, 1970 Judge Edwin Robson found him incompetent to stand trial in the Chicago 15 case, severed his case, and committed him to the federal medical center at Springfield, Missouri.";
$medical->convicted = "No — a competency commitment, not a conviction. Found competent shortly after arriving and freed on bond.";
$medical->sentence = "Committed June 3, 1970 and released on bond August 21, 1970 after being found competent — 79 days in the federal medical prison.";
$medical->setPartialDate("incarceration_date", 1970, 6, 3);
$medical->setPartialDate("release_date", 1970, 8, 21);
$medical->save();

// ---- receipt ---------------------------------------------------------------
$p->refresh()->load("cases.institution");
echo "\nAFTER\n";
echo "  {$p->name}  [{$p->slug}]   full name Edward Charles Hoffmans   AKA {$p->aka}\n";
echo "  died: ".$p->formatPartialDate("death_date")." (year precision -- died BY 2019, see bio)\n";
$total = 0;
foreach ($p->cases->sortBy(fn ($c) => (string) ($c->incarceration_date ?: $c->arrest_date)) as $c) {
    $total += (int) $c->imprisoned_for_days;
    echo "  case  inc=".str_pad((string) ($c->formatPartialDate("incarceration_date") ?: "-"), 14)
        ." rel=".str_pad((string) ($c->formatPartialDate("release_date") ?: "- (not found)"), 15)
        ." days=".str_pad((string) ($c->imprisoned_for_days ?? "null"), 5)
        ." ".($c->institution->name ?? "-")."\n";
}
echo "  counter: {$total} days -- the documented minimum is 483; the Sandstone case counts\n";
echo "  nothing until a real release date is found (still inside on February 16, 1972).\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
