#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/fix_nicole_kissane_case.sh
#
# prisoner:add was a no-op for Nicole Kissane (she already exists
# from Airtable). This script finds her existing record and her
# existing case row (or creates one), and fills in the case fields
# from publicly documented sources:
#
#   - Arrest:    July 24, 2015 (Oakland, CA)
#   - Sentenced: February 16, 2017 (S.D. Cal.)
#   - 21 months federal prison + 3 yrs supervised release + $423,477
#     restitution
#   - Released:  July 20, 2018
#
# Sources: USAO SD California; Earth First! Newswire (Jul 2018);
# Washington Post (Jan 2017).
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Nicole Kissane")
    ->orWhere("name", "like", "%Kissane%")
    ->first();

if (!$p) {
    echo "ERROR: Nicole Kissane not found\n";
    exit(1);
}
echo "Found prisoner: {$p->name} (id={$p->id})\n";

$dirty = false;
if (!$p->state)        { $p->state = "California"; $dirty = true; }
if (!$p->gender)       { $p->gender = "Female";   $dirty = true; }
if ($p->in_custody)    { $p->in_custody = false;  $dirty = true; }
if (!$p->released)     { $p->released = true;     $dirty = true; }
if ($dirty) { $p->save(); echo "Updated prisoner-level fields.\n"; }

$inst = App\Models\Institution::firstOrCreate(
    ["name" => "Federal Bureau of Prisons (location varied)"]
);

$case = $p->cases()->orderBy("created_at")->first();
if (!$case) {
    $case = new App\Models\PrisonerCase(["prisoner_id" => $p->id]);
    echo "No existing case; creating one.\n";
}

$case->institution_id     = $inst->id;
$case->charges            = "Federal conspiracy to violate the Animal Enterprise Terrorism Act (18 U.S.C. ss 43) - co-defendant with Joseph Buddenberg in a 2013-2014 cross-country campaign that released approximately 5,740 mink from fur farms in Idaho, Iowa, Minnesota, Pennsylvania, and elsewhere, and vandalized a San Diego County fur retailer plus the home of its owner.";
$case->arrest_date        = "2015-07-24";
$case->incarceration_date = "2017-02-16";
$case->sentenced_date     = "2017-02-16";
$case->release_date       = "2018-07-20";
$case->sentence           = "21 months federal prison + 3 years supervised release + \$423,477 restitution. Sentenced February 16, 2017 in the U.S. District Court for the Southern District of California; released July 20, 2018.";
$case->convicted          = "Yes - guilty plea, 2016, U.S. District Court for the Southern District of California";
$case->save();

echo "Updated case id={$case->id}: arrest=2015-07-24 incarc=2017-02-16 release=2018-07-20 imprisoned_for_days={$case->imprisoned_for_days}\n";
'

echo
echo "Nicole Kissane case fix complete."
