#!/usr/bin/env bash
#
# Benjamin Gwinn Harris -- he did serve time, and the record said otherwise.
#
# WHAT WAS WRONG
#   The bio read "sentenced to imprisonment and disqualification from office;
#   the imprisonment was remitted by President Andrew Johnson", which reads as
#   though he never saw the inside of a cell. He did. Joshua Kastenberg,
#   A Confederate in Congress: The Civil War Treason Trial of Benjamin Gwinn
#   Harris (McFarland, 2016) -- the only book-length study of the case -- has
#   him convicted by the military commission and sitting in prison for several
#   weeks before Johnson remitted the term.
#
# WHAT IS RECORDED NOW
#   Arrested             May 1865, for sheltering and giving money to two
#                        paroled Confederate soldiers who came to his house in
#                        St. Mary’s County, Maryland
#   Tried                military commission, Washington, D.C., May 1865 --
#                        a military prosecution of a sitting congressman while
#                        the federal civil courts in Maryland were open
#   Sentenced            three years imprisonment and permanent
#                        disqualification from holding any office under the
#                        United States
#   Served               several weeks, then the prison term was remitted by
#                        President Andrew Johnson
#   Afterwards           resumed his seat and served out his term to
#                        March 3, 1867
#
# NO RELEASE DATE IS SET. "Several weeks" is what the sources support; the
# exact days of his confinement and the date of the remission are not
# documented, and inventing a release date to make a counter tick would be
# worse than showing none. Dates that are documented go in at the precision
# they are known -- May 1865, month precision, not a defaulted 1st.
#
# This also clears his imprisoned_for_days, which had gone stale at 59,011 --
# the profile page was rendering "Imprisoned For 161 years 6 months 26 days"
# for a man who served weeks. Saving the case re-runs the computation, which
# now returns null because he is flagged released with no release date.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-benjamin-gwinn-harris.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$matches = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->whereIn("slug", ["benjamin-gwinn-harris", "benjamin-g-harris"])
        ->orWhereRaw("LOWER(name) IN (?, ?)", ["benjamin gwinn harris", "benjamin g. harris"]))
    ->with("cases")
    ->get();

if ($matches->isEmpty()) { echo "NOT FOUND: Benjamin Gwinn Harris\n"; exit(1); }
if ($matches->count() > 1) {
    echo "ABORT: {$matches->count()} records match. Refusing to guess:\n";
    foreach ($matches as $m) { echo "  {$m->slug}  {$m->name}\n"; }
    exit(1);
}

$p = $matches->first();

echo "BEFORE\n";
echo "  bio: ".substr((string) $p->description, 0, 120)."...\n";
foreach ($p->cases as $c) {
    echo "  case  arr=".($c->arrest_date ? $c->arrest_date->toDateString() : "-")
        ."  inc=".($c->incarceration_date ? $c->incarceration_date->toDateString() : "-")
        ."  rel=".($c->release_date ? $c->release_date->toDateString() : "-")
        ."  days=".($c->imprisoned_for_days ?? "null")."\n";
}

$p->description = "Benjamin Gwinn Harris was a Maryland congressman from St. Mary’s County and an outspoken opponent of the war, censured by the House of Representatives on April 9, 1864 for treasonable utterances. In May 1865 he was arrested for sheltering two paroled Confederate soldiers who came to his house and giving them money, and was tried before a military commission in Washington — a military prosecution of a sitting member of Congress at a time when the federal civil courts in Maryland were open and functioning. He was convicted, sentenced to three years imprisonment and permanent disqualification from holding any office under the United States, and jailed. He served several weeks before President Andrew Johnson remitted the prison term; the exact dates of his confinement are not documented. He resumed his seat and served out his term to March 3, 1867.";
$p->in_custody = false;
$p->awaiting_trial = false;
$p->released = true;
$p->save();

$c = $p->cases->first() ?? $p->cases()->create([]);
$c->charges = "Aiding and harboring two paroled Confederate soldiers — sheltering them at his home in St. Mary’s County, Maryland and giving them money, shortly after the surrender.";
$c->convicted = "Yes — convicted by a military commission at Washington, D.C. in May 1865, while the federal civil courts in Maryland were open.";
$c->sentence = "Three years imprisonment and permanent disqualification from holding any office under the United States. He was jailed and served several weeks before President Andrew Johnson remitted the prison term; the exact dates of his confinement and of the remission are not documented, so no release date is recorded. He resumed his seat in Congress and served out his term to March 3, 1867.";
$c->setPartialDate("arrest_date", 1865, 5);
$c->setPartialDate("incarceration_date", 1865, 5);
$c->setPartialDate("release_date", null);
$c->save();   // recomputes imprisoned_for_days -- null, since he is released with no release date

$p->refresh()->load("cases");
echo "\nAFTER\n";
foreach ($p->cases as $c) {
    echo "  case  arr=".($c->formatPartialDate("arrest_date") ?: "-")
        ."  inc=".($c->formatPartialDate("incarceration_date") ?: "-")
        ."  rel=".($c->formatPartialDate("release_date") ?: "- (not documented)")
        ."  days=".($c->imprisoned_for_days ?? "null (no counter shown)")."\n";
}
echo "  bio updated to record the weeks he actually served.\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
