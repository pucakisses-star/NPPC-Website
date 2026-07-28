#!/usr/bin/env bash
#
# David José Huerta -- birth year, full name, and the missing custody date.
#
# WHAT WAS MISSING
#   The case had an arrest date of June 6, 2025 and a release date of June 9,
#   but NO incarceration date. PrisonerCase::saving computes imprisoned_for_days
#   from the incarceration date alone, so the three days he was actually held
#   counted as nothing: imprisonedFor was 0 and the profile showed no counter at
#   all. He was taken into custody at the scene and held until the bond hearing,
#   so the arrest date is the incarceration date.
#
# THE CUSTODY RECORD
#   June 6, 2025    detained in Los Angeles while blocking an ICE vehicle
#                   during the multi-day immigration raids
#   June 8, 2025    federal felony complaint filed -- a complaint, not an
#                   indictment, which is what the case row now says
#   June 9, 2025    initial court appearance; released the same day on a
#                   \$50,000 bond
#                   = 3 days
#
# ALSO SET
#   Birth year      1967, at YEAR precision, which is all the federal court
#                   record gives. Year precision stores January 1 internally,
#                   so the age the site computes may be a year high depending on
#                   where his birthday falls.
#   Full name       first David, middle José, last Huerta. The display name
#                   stays "David Huerta" and the slug does not move, so the
#                   photo already attached still resolves; the profile shows
#                   "David José Huerta" in the full-name row above the birth
#                   date.
#   Affiliation     Service Employees International Union (SEIU) -- the record
#                   had none, though the bio has led with his SEIU California
#                   presidency all along. New taxonomy term; nothing SEIU-shaped
#                   existed.
#
# NOT CHANGED. awaiting_trial stays false. The case text says "case pending",
# but that was written in 2025 and nothing here establishes where the
# prosecution stands now. Flipping it would put an Awaiting Trial badge on the
# profile and pull him into the awaiting-trial filters on the strength of a
# year-old note.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-david-huerta.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$matches = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->where("slug", "david-huerta")
        ->orWhereRaw("LOWER(name) IN (?, ?)", ["david huerta", "david josé huerta"]))
    ->with("cases")
    ->get();

if ($matches->isEmpty()) { echo "NOT FOUND: David Huerta\n"; exit(1); }
if ($matches->count() > 1) {
    echo "ABORT: {$matches->count()} records match. Refusing to guess:\n";
    foreach ($matches as $m) { echo "  {$m->slug}  {$m->name}\n"; }
    exit(1);
}

$p = $matches->first();

echo "BEFORE\n";
echo "  born=".($p->birthdate ? $p->birthdate->toDateString() : "-")."  age=".($p->age ?? "-")."\n";
foreach ($p->cases as $c) {
    echo "  case  arr=".($c->arrest_date ? $c->arrest_date->toDateString() : "-")
        ."  inc=".($c->incarceration_date ? $c->incarceration_date->toDateString() : "-")
        ."  rel=".($c->release_date ? $c->release_date->toDateString() : "-")
        ."  days=".($c->imprisoned_for_days ?? "null")."\n";
}

$p->first_name = "David";
$p->middle_name = "José";
$p->last_name = "Huerta";
$p->gender = "Male";
$p->state = "California";
$p->era = "2020s";
$p->setPartialDate("birthdate", 1967);   // year precision -- the court record gives no more
$affs = is_array($p->affiliation) ? $p->affiliation : [];
if (! in_array("Service Employees International Union (SEIU)", $affs, true)) {
    $affs[] = "Service Employees International Union (SEIU)";
}
$p->affiliation = array_values($affs);
$p->in_custody = false;
$p->released = true;
$p->save();   // name is unchanged, so the slug and the attached photo stay put

$c = $p->cases->first(fn ($x) => stripos((string) $x->charges, "impede") !== false)
    ?? $p->cases->first()
    ?? $p->cases()->create([]);
$c->indicted = "No — charged by federal felony complaint filed June 8, 2025, not by grand jury indictment.";
$c->setPartialDate("arrest_date", 2025, 6, 6);
$c->setPartialDate("incarceration_date", 2025, 6, 6);   // detained at the scene, held until the bond hearing
$c->setPartialDate("release_date", 2025, 6, 9);
$c->sentence = "Detained at the scene on June 6, 2025 and held until his initial court appearance on June 9, when he was released on a \$50,000 bond — three days in custody. A federal felony complaint had been filed on June 8. No conviction or sentence; the case was pending as of the release.";
$c->save();

$p->refresh()->load("cases");
echo "\nAFTER\n";
echo "  {$p->name}  [{$p->slug}]\n";
echo "  full name:    ".trim($p->first_name." ".$p->middle_name." ".$p->last_name)."\n";
echo "  born:         ".$p->formatPartialDate("birthdate")."   (year precision; computed age {$p->age} may be a year high)\n";
echo "  affiliation:  ".implode(", ", $p->affiliation)."\n";
$total = 0;
foreach ($p->cases as $c) {
    $total += (int) $c->imprisoned_for_days;
    echo "  case  arr=".($c->formatPartialDate("arrest_date") ?: "-")
        ."  inc=".($c->formatPartialDate("incarceration_date") ?: "-")
        ."  rel=".($c->formatPartialDate("release_date") ?: "-")
        ."  days=".($c->imprisoned_for_days ?? "null")."\n";
}
echo "  counter: {$total} days (was 0 -- no counter was shown at all)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
