#!/usr/bin/env bash
#
# Christina Leigh Reid (known as Chris Reid) -- corrected record.
#
#   Gender        Female (was recorded as Male)
#   BOP number    84263-011
#   Born          c. 1964 (year precision)
#   Arrested      1989-07-12
#   Convicted     1990-06-18
#   Sentenced     1990-08-20  -- 41 months
#   Incarcerated  approximately 1991-06-11
#   Released      1994-06-21  (1,106 days, about 36 months of a 41-month
#                 sentence -- consistent with federal good-conduct credit)
#
# Written as an upsert: it updates the existing record, or recreates it if
# remove-chris-reid.sh has already been run. Idempotent.
#
#   bash database/data/update-christina-reid.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->where("slug", "christina-leigh-reid")
        ->orWhere("slug", "chris-reid")
        ->orWhereRaw("LOWER(name) IN (?, ?)", ["chris reid", "christina leigh reid"]))
    ->first();

if (! $p) {
    $p = Prisoner::create([
        "name" => "Christina Leigh Reid",
        "state" => "California",
        "ideologies" => ["Irish Republicanism", "Anti-Imperialism"],
        "affiliation" => ["Irish Republican Army"],
    ]);
    echo "recreated the record (slug {$p->slug}) -- it had been removed.\n";
} else {
    echo "updating {$p->name} (slug {$p->slug}).\n";
}

$p->name = "Christina Leigh Reid";
$p->first_name = "Christina";
$p->middle_name = "Leigh";
$p->last_name = "Reid";
$p->aka = "Chris Reid";
$p->gender = "Female";
$p->inmate_number = "84263-011";
if (! $p->state) { $p->state = "California"; }
if (! $p->era) { $p->era = "1990s"; }
$p->setPartialDate("birthdate", 1964, null, null);   // c. 1964 -- year only
$p->in_custody = false;
$p->awaiting_trial = false;
$p->released = true;
$p->description = "Christina Leigh Reid, known as Chris Reid, was an Irish republican prisoner held on United States federal charges relating to support for the Irish Republican Army, and imprisoned at FCI Pleasanton in California. Arrested on July 12, 1989, she was convicted on June 18, 1990 and sentenced on August 20, 1990 to 41 months. She entered custody in approximately June 1991 and was released on June 21, 1994. Her case was documented in the Prairie Fire Organizing Committee magazine Breakthrough.";
$p->save();   // regenerates the slug from the corrected name
echo "  {$p->name} (aka {$p->aka}), Female, BOP {$p->inmate_number}, born c.1964, slug {$p->slug}\n";

$inst = Institution::firstOrCreate(
    ["name" => "FCI Pleasanton"],
    ["city" => "Pleasanton", "state" => "California"],
);

$c = $p->cases()->orderBy("created_at")->first();
if (! $c) { $c = $p->cases()->make(); $c->prisoner_id = $p->id; }
$c->charges = "Federal charges relating to Irish republican activity — support for the Irish Republican Army.";
$c->institution_id = $inst->id;
$c->convicted = "Yes — convicted June 18, 1990";
$c->sentence = "Forty-one months, imposed August 20, 1990. Entered custody in approximately June 1991 and released June 21, 1994 -- about 36 months served, consistent with federal good-conduct credit.";
$c->setPartialDate("arrest_date", 1989, 7, 12);
$c->setPartialDate("sentenced_date", 1990, 8, 20);
$c->setPartialDate("incarceration_date", 1991, 6, 11);
$c->setPartialDate("release_date", 1994, 6, 21);
$c->save();
echo "  case: arrested 1989-07-12, sentenced 1990-08-20, custody 1991-06-11 -> 1994-06-21, days={$c->imprisoned_for_days} (expected 1106)\n";

if (empty($p->photo)) {
    echo "  NOTE: no photo attached. A period portrait is said to exist -- send the URL and it can be cropped in.\n";
}
if ($p->sort_order == 0) {
    echo "  NOTE: sort_order is 0 -- run php artisan prisoners:auto-place-zero-sort to position the record.\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
