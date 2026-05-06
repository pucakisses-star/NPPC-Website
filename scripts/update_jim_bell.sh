#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/update_jim_bell.sh
#
# Appends additional case-history detail to Jim Bell's existing
# description (does NOT overwrite) and adds a case row covering his
# two federal prosecutions:
#
#   1. 1997-1999: IRS-retaliation case (Western District of
#      Washington). Convicted in 1997 of misdemeanor obstruction
#      of IRS officials and a felony false-identification charge.
#      Sentenced to 11 months federal prison. Released 1999.
#
#   2. 2000-2012: Re-arrested November 2000. Convicted in 2001
#      under 18 U.S.C. ss 2261A (interstate stalking) of IRS
#      agents and a separate Treasury Department official.
#      Sentenced to 10 years federal prison. Released April 2012
#      from FCI Phoenix.
#
# Born April 22, 1958. Author of the 1995-96 cypherpunk essay
# "Assassination Politics," which was central to the
# governments theory in the second prosecution.
#
# Sources: United States v. Bell, 217 F.3d 845 (9th Cir. 2000);
# United States v. Bell, 303 F.3d 1187 (9th Cir. 2002); BOP
# inmate locator (register no. 26906-086).
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Jim Bell")
    ->orWhere("name", "like", "%Jim Bell%")
    ->orWhere("name", "like", "%James Dalton Bell%")
    ->orWhere("aka", "like", "%Jim Bell%")
    ->first();

if (!$p) {
    echo "ERROR: Jim Bell not found\n";
    exit(1);
}
echo "Found prisoner: {$p->name} (id={$p->id})\n";

// Append case-history paragraph to existing description (only if
// not already present). Idempotent.
$marker = "Convicted in 1997";
$append = "\n\nBell was prosecuted in federal court twice in connection with his anti-IRS organizing and his Assassination Politics writings. In 1997 he was convicted in the U.S. District Court for the Western District of Washington of misdemeanor obstruction of IRS officials and a felony false-identification charge, and was sentenced to 11 months in federal prison; he was released in 1999. He was rearrested in November 2000 and was convicted in 2001 under 18 U.S.C. ss 2261A (interstate stalking) of two IRS revenue officers and a Treasury Department investigator. Sentenced to 10 years federal prison, he was released in April 2012 from FCI Phoenix (BOP register number 26906-086).";

$dirty = false;
if (!$p->birthdate) {
    $p->birthdate = "1958-04-22";
    $dirty = true;
}
if (strpos((string) $p->description, $marker) === false) {
    $p->description = trim((string) $p->description) . $append;
    $dirty = true;
}
if (!$p->inmate_number) {
    $p->inmate_number = "26906-086";
    $dirty = true;
}
if ($p->in_custody) { $p->in_custody = false; $dirty = true; }
if (!$p->released)  { $p->released   = true;  $dirty = true; }
if ($dirty) {
    $p->save();
    echo "Updated prisoner record.\n";
}

// Add a case row if none exists yet for the 2000-2012 federal
// prosecution (the longer of the two).
$existing = $p->cases()->where("incarceration_date", ">=", "2000-01-01")->first();
if ($existing) {
    echo "Case for 2000s prosecution already exists (id={$existing->id}); skipping insert.\n";
    exit(0);
}

$inst = App\Models\Institution::firstOrCreate(
    ["name" => "Federal Correctional Institution, Phoenix"],
    ["city" => "Phoenix", "state" => "Arizona"]
);

App\Models\PrisonerCase::create([
    "prisoner_id"        => $p->id,
    "institution_id"     => $inst->id,
    "charges"            => "Federal interstate stalking, 18 U.S.C. ss 2261A - the government alleged that Bell harassed two IRS revenue officers and a Treasury Department investigator. The prosecution case relied heavily on Bells 1995-96 cypherpunk essay Assassination Politics as evidence of intent. (Earlier 1997 prosecution: misdemeanor obstruction of IRS officials and felony false-identification, 11 months federal prison, Western District of Washington.)",
    "arrest_date"        => "2000-11-17",
    "incarceration_date" => "2000-11-17",
    "sentenced_date"     => "2001-08-13",
    "release_date"       => "2012-04-12",
    "sentence"           => "10 years federal prison; served at multiple BOP facilities, released April 2012 from FCI Phoenix. BOP register number 26906-086.",
    "convicted"          => "Yes - federal jury verdict, U.S. District Court for the Western District of Washington, April 11 2001; affirmed United States v. Bell, 303 F.3d 1187 (9th Cir. 2002)",
]);

echo "Inserted Jim Bell 2000-2012 federal case (FCI Phoenix).\n";
'

echo
echo "Jim Bell update complete."
