#!/usr/bin/env bash
#
# Fergie Chambers (James Cox Chambers Jr.) -- restore the in-custody status and
# give his case an incarceration date.
#
# TWO SEPARATE PROBLEMS
#
# 1. The case has an arrest_date (July 10, 2026, Ibiza) and no
#    incarceration_date. PrisonerCase::saving computes imprisoned_for_days from
#    incarceration_date only -- arrest_date is never used -- so his detention
#    counter sits at null and he contributes nothing to the day totals, the
#    live cost ticker, or the "in custody today" figures, even though he has
#    been held since the day of the arrest. He was arrested and held on the
#    same day, so the arrest date IS the incarceration date. Copied across,
#    keeping the same date precision.
#
# 2. Every "in custody" list on the site -- the database filter, the birthday
#    calendar, prisoner outreach, the state pages, the trackers -- reads the
#    prisoner.in_custody boolean directly. prisoners:add-fergie-chambers sets
#    that flag to true, but only on the branch that CREATES the record; if a
#    Fergie Chambers row already existed the command logs "Skipped (already
#    exists)", attaches the portrait and returns without touching any flag. So
#    a record created by any other route keeps whatever status it had. This
#    script sets the flags explicitly and prints the previous values, so the
#    output shows which of the two problems was actually in play.
#
# It also clears under_review if it is set, since that hides a record from the
# public site entirely, and reports that loudly rather than doing it silently.
#
# STATUS SET: in_custody = true, awaiting_trial = true, released = false.
# He was arrested on July 10, 2026 on a sealed U.S. indictment and ordered held
# in Spain pending an extradition hearing. He has not been tried or convicted.
#
# The script ends with a read-only audit of every OTHER in-custody prisoner
# whose case has an arrest date but no incarceration date -- the same silent
# counter bug. Nothing else is written; it is a list to work through.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-fergie-chambers-custody.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;

$matches = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->whereIn("slug", ["fergie-chambers", "james-cox-chambers-jr"])
        ->orWhereRaw("LOWER(name) IN (?, ?)", ["fergie chambers", "james cox chambers jr."])
        ->orWhere("aka", "like", "%James Cox Chambers%"))
    ->with("cases")
    ->get();

if ($matches->isEmpty()) {
    echo "NOT FOUND: Fergie Chambers.\n";
    echo "The record has never been created on this database. Run:\n";
    echo "  php artisan prisoners:add-fergie-chambers\n";
    exit(1);
}
if ($matches->count() > 1) {
    echo "ABORT: {$matches->count()} records match. Refusing to guess:\n";
    foreach ($matches as $m) { echo "  {$m->slug}  {$m->name}\n"; }
    exit(1);
}

$p = $matches->first();

echo "BEFORE\n";
echo "  {$p->name}  [{$p->slug}]  aka: ".($p->aka ?: "-")."\n";
echo "  in_custody=".($p->in_custody ? "yes" : "no")
    ."  awaiting_trial=".($p->awaiting_trial ? "yes" : "no")
    ."  released=".($p->released ? "yes" : "no")
    ."  under_review=".($p->under_review ? "YES (hidden from the site)" : "no")
    ."  imprisoned_or_exiled=".($p->imprisoned_or_exiled ? "yes" : "no")."\n";
foreach ($p->cases as $c) {
    echo "  case  arrest=".($c->arrest_date ? $c->arrest_date->toDateString() : "-")
        ."  incarcerated=".($c->incarceration_date ? $c->incarceration_date->toDateString() : "-")
        ."  release=".($c->release_date ? $c->release_date->toDateString() : "-")
        ."  days=".($c->imprisoned_for_days ?? "null")."\n";
}

// ---- status ---------------------------------------------------------------
if ($p->under_review) {
    echo "\nclearing under_review -- the record was hidden from the public site\n";
    $p->under_review = false;
}
$p->in_custody = true;
$p->awaiting_trial = true;
$p->released = false;
$p->save();   // the saving hook re-derives imprisoned_or_exiled from these

// ---- arrest date becomes the incarceration date ---------------------------
// He was taken into Spanish custody on the day of the arrest and ordered held
// pending extradition, so the two dates are the same. Only fills a gap; an
// incarceration date that is already set is left alone.
$filled = 0;
foreach ($p->cases as $c) {
    if ($c->incarceration_date || ! $c->arrest_date) { continue; }
    $c->incarceration_date = $c->arrest_date;
    $c->mirrorDatePrecision("arrest_date", "incarceration_date");
    $c->save();   // recomputes imprisoned_for_days, now counting to today
    $filled++;
}
if ($filled === 0) { echo "\nno case needed an incarceration date\n"; }

// ---- receipt --------------------------------------------------------------
$p->refresh()->load("cases");
echo "\nAFTER\n";
echo "  in_custody=".($p->in_custody ? "yes" : "no")
    ."  awaiting_trial=".($p->awaiting_trial ? "yes" : "no")
    ."  released=".($p->released ? "yes" : "no")
    ."  under_review=".($p->under_review ? "yes" : "no")
    ."  imprisoned_or_exiled=".($p->imprisoned_or_exiled ? "yes" : "no")."\n";
foreach ($p->cases as $c) {
    echo "  case  arrest=".($c->arrest_date ? $c->arrest_date->toDateString() : "-")
        ."  incarcerated=".($c->incarceration_date ? $c->incarceration_date->toDateString() : "-")
        ."  release=".($c->release_date ? $c->release_date->toDateString() : "-")
        ."  days=".($c->imprisoned_for_days ?? "null")." (counting to today)\n";
}

// ---- read-only audit of the same bug elsewhere ----------------------------
echo "\n---- audit: other in-custody prisoners with an arrest date but no incarceration date ----\n";
echo "Nothing below is modified. Each one has a detention counter stuck at null.\n\n";

$stuck = PrisonerCase::query()
    ->whereNotNull("arrest_date")
    ->whereNull("incarceration_date")
    ->whereNull("release_date")
    ->whereHas("prisoner", fn ($q) => $q->withoutGlobalScopes()->where("in_custody", true))
    ->with(["prisoner" => fn ($q) => $q->withoutGlobalScopes()])
    ->get()
    ->filter(fn ($c) => $c->prisoner && $c->prisoner->id !== $p->id)
    ->sortByDesc(fn ($c) => (string) $c->arrest_date);

if ($stuck->isEmpty()) {
    echo "  none -- Fergie Chambers was the only one.\n";
} else {
    foreach ($stuck as $c) {
        echo "  ".str_pad($c->arrest_date->toDateString(), 12)." "
            .str_pad((string) $c->prisoner->name, 32)." /prisoner/".$c->prisoner->slug."\n";
    }
    echo "\n  ".$stuck->count()." case(s). Copying arrest_date to incarceration_date is only\n";
    echo "  correct where the person was held from the day of the arrest -- someone bailed\n";
    echo "  out the same afternoon should not start a counter. Worth going through by hand.\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
