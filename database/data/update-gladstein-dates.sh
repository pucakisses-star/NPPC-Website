#!/usr/bin/env bash
#
# Fill in Richard Gladstein's dates and custody, per case info supplied by the
# site owner:
#   Born  : December 28, 1908 (New Haven, Connecticut)
#   Died  : May 16, 1981 (San Francisco, California)
#   Custody: surrendered April 24, 1952 (with the other Foley Square defense
#            lawyers, after the Supreme Court upheld their contempt convictions);
#            held at the New York House of Detention, then transferred to the
#            Federal Correctional Institution, Texarkana, Texas; released
#            September 23, 1952 — 152 days served on a six-month sentence.
#
# Idempotent. Run from the repo root:
#   bash database/data/update-gladstein-dates.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "richard-gladstein")->first();
if (! $p) { echo "richard-gladstein not found.\n"; return; }

$p->setPartialDate("birthdate", 1908, 12, 28);
$p->setPartialDate("death_date", 1981, 5, 16);
$p->in_custody = false; $p->released = true;
$p->save();

$inst = \App\Models\Institution::firstOrCreate(
    ["name" => "Federal Correctional Institution, Texarkana"],
    ["city" => "Texarkana", "state" => "Texas"]
);

$c = $p->cases()->first() ?: (new \App\Models\PrisonerCase(["prisoner_id" => $p->id]));
$c->prisoner_id = $p->id;
$c->institution_id = $inst->id;
$c->setPartialDate("incarceration_date", 1952, 4, 24);
$c->setPartialDate("release_date", 1952, 9, 23);
$c->imprisoned_for_days = 152;
$c->sentence = "6 months (served 152 days; surrendered April 24, 1952, held at the New York House of Detention then FCI Texarkana, released September 23, 1952)";
if (empty($c->convicted)) { $c->convicted = "Yes — contempt of court (Foley Square Smith Act trial defense counsel); convictions upheld by the Supreme Court"; }
$c->save();

$p->refresh();
echo "Updated richard-gladstein: born 1908-12-28, died 1981-05-16, 152 days (1952-04-24 to 1952-09-23).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Richard Gladstein dates and custody updated."
