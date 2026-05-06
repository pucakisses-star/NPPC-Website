#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/update_bill_haywood.sh
#
# Updates the existing Bill Haywood prisoner record with full birth/death
# data and attaches the 1918 IWW Chicago trial + 1921 USSR exile case.
#
# Sources:
#   - Bill Haywood, Wikipedia (b. Feb 4, 1869, Salt Lake City; d. May 18,
#     1928, Moscow). https://en.wikipedia.org/wiki/Bill_Haywood
#   - April 1918 mass federal prosecution of 101 IWW members in U.S.
#     District Court for the Northern District of Illinois, before Judge
#     Kenesaw Mountain Landis; verdict August 30, 1918; sentenced Sept
#     13, 1918 to 20 years federal prison + $30,000 fine
#   - Imprisoned at USP Leavenworth from Sept 13, 1918 for approximately
#     one year; released on appeal bond around Sept 1919
#   - Jumped bail on March 31, 1921 and fled to the USSR; bond forfeited
#   - Lived in exile in Moscow until his death May 18, 1928 (became labor
#     advisor to the Bolshevik government 1921-1923)
#
# Idempotent: re-running this script updates dates on the existing case
# row rather than inserting a duplicate.
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Bill Haywood")->first();
if (!$p) {
    echo "ERROR: Bill Haywood not found in prisoners table\n";
    exit(1);
}

// Update biographical fields
$p->birthdate            = "1869-02-04";
$p->death_date           = "1928-05-18";
$p->age                  = 59;
$p->state                = "Illinois";
$p->in_custody           = false;
$p->released             = false;
$p->in_exile             = true;
$p->currently_in_exile   = false;
$p->imprisoned_or_exiled = true;
$p->saveQuietly();

echo "Updated Bill Haywood prisoner record (id={$p->id})\n";

// Locate or create the Leavenworth institution record
$inst = App\Models\Institution::firstOrCreate(
    ["name" => "United States Penitentiary, Leavenworth"],
    ["city" => "Leavenworth", "state" => "Kansas"]
);

$caseAttrs = [
    "institution_id"     => $inst->id,
    "charges"            => "Federal Espionage Act of 1917 and conspiracy - April-August 1918 mass federal prosecution of 101 Industrial Workers of the World members in U.S. District Court for the Northern District of Illinois, before Judge Kenesaw Mountain Landis. Haywood and his co-defendants were charged with sedition, opposition to the World War I draft, and conspiracy to obstruct the war effort.",
    "arrest_date"        => "1917-09-05",
    "incarceration_date" => "1918-09-13",
    "sentenced_date"     => "1918-09-13",
    "release_date"       => "1919-09-13",
    "sentence"           => "20 years federal prison and \$30,000 fine; served approximately one year at USP Leavenworth (Sept 1918 - Sept 1919); released on \$15,000 appeal bond posted by Chicago millionaire supporter William Bross Lloyd; jumped bail March 31, 1921 and fled to the Soviet Union; bond forfeited.",
    "convicted"          => "Yes - federal jury verdict, U.S. District Court for the Northern District of Illinois, August 30, 1918",
    "judge"              => "Kenesaw Mountain Landis",
    "in_exile_since"     => "1921-03-31",
    "end_of_exile"       => "1928-05-18",
];

// Idempotent: update existing matching case, otherwise insert new
$existingCase = App\Models\PrisonerCase::where("prisoner_id", $p->id)
    ->where("charges", "like", "%Espionage Act of 1917%IWW%")
    ->first();

if ($existingCase) {
    foreach ($caseAttrs as $k => $v) {
        $existingCase->{$k} = $v;
    }
    $existingCase->save();
    echo "Updated existing 1918 IWW Chicago trial + 1921-1928 USSR exile case (id={$existingCase->id})\n";
    echo "  imprisoned_for_days now = {$existingCase->imprisoned_for_days} (~365 = 1 yr at Leavenworth)\n";
} else {
    $caseAttrs["prisoner_id"] = $p->id;
    $newCase = App\Models\PrisonerCase::create($caseAttrs);
    echo "Inserted 1918 IWW Chicago trial + 1921-1928 USSR exile case (id={$newCase->id})\n";
    echo "  imprisoned_for_days = {$newCase->imprisoned_for_days} (~365 = 1 yr at Leavenworth)\n";
}
'

echo
echo "Bill Haywood update complete."
