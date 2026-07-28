#!/usr/bin/env bash
#
# John LaForge -- correct a phantom 36-year imprisonment and add the 1984
# Sperry case.
#
# Not requested; found while correcting Barbara Katt, his co-defendant. Run it
# or do not, but the record as it stands says something false about a living
# man.
#
# PROBLEM 1 -- one case asserting 36 continuous years in custody
#   His single case row runs incarceration 1987-04-14 to release 2023-07-31,
#   which the page renders as "Imprisoned For 36 years 3 months 17 days"
#   (13,257 days). Its own charges field lists Project ELF trespasses in
#   Wisconsin and Michigan AND Buechel Air Base in Germany, and its sentence
#   field says "cumulative years in custody across multiple jurisdictions".
#   So the row is not one imprisonment at all -- it is decades of separate
#   short custodies collapsed into a single span, and the span then gets
#   counted as though he never came home.
#
#   The documented long stretch is roughly seven months at Koblenz Open Prison
#   in 2023. Exact commitment and release dates for that term are not in the
#   record, and neither are the dates of the individual ELF custodies, so this
#   script does NOT invent a replacement. It keeps the 1987 arrest date and the
#   charges, clears the incarceration and release dates that together make the
#   false continuous-custody claim, and rewrites the sentence text to say
#   plainly that the row needs splitting into the separate custodies. The
#   counter goes to null rather than to a fabricated number.
#
# PROBLEM 2 -- his most famous case is missing
#   The 1984 Sperry Software Pair action, for which he and Katt were tried
#   together, is not on his record at all. Added here with the same documented
#   facts used for her:
#
#     Arrested    August 10, 1984, at the Sperry Defense Systems plant,
#                 Eagan, Minnesota
#     Convicted   October 11, 1984, by jury, felony depredation or destruction
#                 of government property, 18 U.S.C. section 1361
#                 United States v. LaForge and Katt, No. CR-84-66 (D. Minn.)
#     Sentenced   November 8, 1984 by Judge Miles W. Lord -- six months
#                 imprisonment entirely suspended, six months unsupervised
#                 probation, no prison term served
#
#   A sentence about the action is added to his bio, which did not mention it.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-john-laforge.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$matches = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->where("slug", "john-laforge")
        ->orWhereRaw("LOWER(name) = ?", ["john laforge"]))
    ->with("cases")
    ->get();

if ($matches->isEmpty()) { echo "NOT FOUND: John LaForge\n"; exit(1); }
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

$p->in_custody = false;
$p->awaiting_trial = false;
$p->released = true;

if (stripos((string) $p->description, "Sperry") === false) {
    $p->description = "On August 10, 1984 LaForge and Barbara Katt walked into the Sperry Defense Systems plant at Eagan, Minnesota dressed as corporate quality-control inspectors, damaged two prototype guidance computers intended for Trident submarines and F-4G aircraft with household hammers, poured blood over the equipment and delivered a citizens indictment accusing the company of violating international law. A jury convicted them on October 11, 1984 of felony destruction of government property; on November 8 Judge Miles W. Lord suspended the entire six-month sentence in favour of six months of unsupervised probation, in a sentencing speech attacking nuclear-weapons production and the contrast with Sperry’s own overbilling of the government. Neither served prison time for the action. "
        .trim((string) $p->description);
}
$p->save();

// ---- the phantom 1987-2023 span -------------------------------------------
$lumped = $p->cases->first(fn ($c) => $c->incarceration_date
    && $c->release_date
    && $c->incarceration_date->year <= 1990
    && $c->release_date->year >= 2020);

if ($lumped) {
    $lumped->sentence = "NEEDS SPLITTING. This row collapses decades of separate short custodies — Project ELF trespasses in Wisconsin and Michigan, and the Buechel Air Base actions in Germany — into a single span, which the site then counted as one unbroken imprisonment of over thirty-six years. The documented long term is roughly seven months at Koblenz Open Prison in 2023, after he became the first U.S. citizen since the Cold War imprisoned in Germany for anti-nuclear protest. Exact commitment and release dates for that term, and for the individual ELF custodies, are not recorded, so no dates are asserted here. Each custody should become its own case as the dates are found.";
    $lumped->setPartialDate("incarceration_date", null);
    $lumped->setPartialDate("release_date", null);
    $lumped->save();
    echo "\ncleared the 1987-2023 continuous-custody claim (arrest date and charges kept)\n";
} else {
    echo "\nno lumped multi-decade case found -- already split?\n";
}

// ---- the 1984 Sperry case --------------------------------------------------
$sperry = $p->cases()->where("charges", "like", "%Sperry%")->first()
    ?? $p->cases()->create([]);
$sperry->charges = "Felony depredation or destruction of government property, 18 U.S.C. section 1361 — the Sperry Software Pair plowshares action at the Sperry Defense Systems plant, Eagan, Minnesota, damaging two prototype guidance computers for Trident submarines and F-4G aircraft. Damage put at roughly \$34,000 to \$36,000.";
$sperry->plead = "Not guilty";
$sperry->convicted = "Yes — convicted by a jury on October 11, 1984 in United States v. LaForge and Katt, No. CR-84-66 (D. Minn.).";
$sperry->sentence = "Six months imprisonment, entirely suspended, plus six months of unsupervised probation — no prison term was served. Arrested at the scene on August 10, 1984 and evidently released pending trial; the date of that release is not documented, so none is recorded here and this case adds no days to the total.";
$sperry->setPartialDate("arrest_date", 1984, 8, 10);
$sperry->setPartialDate("incarceration_date", 1984, 8, 10);
$sperry->setPartialDate("release_date", null);
$sperry->setPartialDate("sentenced_date", 1984, 11, 8);
$sperry->save();

$p->refresh()->load("cases");
echo "\nAFTER\n";
$total = 0;
foreach ($p->cases->sortBy(fn ($c) => (string) $c->arrest_date) as $c) {
    $total += (int) $c->imprisoned_for_days;
    echo "  case  arr=".($c->formatPartialDate("arrest_date") ?: "-")
        ."  inc=".($c->formatPartialDate("incarceration_date") ?: "-")
        ."  rel=".($c->formatPartialDate("release_date") ?: "-")
        ."  days=".($c->imprisoned_for_days ?? "null")."\n";
}
echo "  counter now: {$total} days (was 13,257 -- 36 years 3 months 17 days)\n";
echo "  His real Koblenz term of roughly seven months in 2023 is still unrecorded as a case.\n";
echo "  Find the commitment and release dates and it can be added properly.\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
