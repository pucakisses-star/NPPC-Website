#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/update_bill_haywood.sh
#
# Updates the existing Bill Haywood prisoner record with full birth/death
# data and attaches the 1918 IWW Chicago trial + 1921 USSR exile case.
#
# Sources:
#   - Bill Haywood, Wikipedia (b. Feb 4, 1869, Salt Lake City; d. May 18,
#     1928, Moscow)
#   - April 1918 mass federal prosecution of 101 IWW members in U.S.
#     District Court for the Northern District of Illinois, before Judge
#     Kenesaw Mountain Landis; verdict August 30, 1918; sentenced Sept
#     13, 1918 to 20 years federal prison + $30,000 fine
#   - $15,000 appeal bond posted by William Bross Lloyd; bond forfeited
#     when Haywood jumped bail in March/April 1921 and fled to the USSR
#   - Half of his ashes interred in the Kremlin Wall; half buried near
#     the Haymarket Martyrs' Monument at Forest Home Cemetery in Chicago
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Bill Haywood")->first();
if (!$p) {
    echo "ERROR: Bill Haywood not found in prisoners table\n";
    exit(1);
}

// Update biographical fields
$p->birthdate           = "1869-02-04";
$p->death_date          = "1928-05-18";
$p->age                 = 59;
$p->state               = "Illinois";
$p->in_custody          = false;
$p->released            = false;
$p->in_exile            = true;
$p->currently_in_exile  = false;
$p->imprisoned_or_exiled = true;
$p->saveQuietly();

echo "Updated Bill Haywood prisoner record (id={$p->id})\n";

// Locate or create the Leavenworth institution record
$inst = App\Models\Institution::firstOrCreate(
    ["name" => "United States Penitentiary, Leavenworth"],
    ["city" => "Leavenworth", "state" => "Kansas"]
);

// Avoid duplicating the case if this script is re-run
$existingCase = App\Models\PrisonerCase::where("prisoner_id", $p->id)
    ->where("charges", "like", "%Espionage Act of 1917%IWW%")
    ->first();

if ($existingCase) {
    echo "Case already exists (id={$existingCase->id}); skipping case insert.\n";
    exit(0);
}

App\Models\PrisonerCase::create([
    "prisoner_id"        => $p->id,
    "institution_id"     => $inst->id,
    "charges"            => "Federal Espionage Act of 1917 and conspiracy - April-August 1918 mass federal prosecution of 101 Industrial Workers of the World members in U.S. District Court for the Northern District of Illinois, before Judge Kenesaw Mountain Landis. Haywood and his co-defendants were charged with sedition, opposition to the World War I draft, and conspiracy to obstruct the war effort.",
    "arrest_date"        => "1917-09-05",
    "incarceration_date" => "1918-09-13",
    "sentenced_date"     => "1918-09-13",
    "sentence"           => "20 years federal prison and \$30,000 fine; released on \$15,000 appeal bond posted by Chicago millionaire supporter William Bross Lloyd; jumped bail and fled to the Soviet Union in spring 1921; bond forfeited.",
    "convicted"          => "Yes - federal jury verdict, U.S. District Court for the Northern District of Illinois, August 30, 1918",
    "judge"              => "Kenesaw Mountain Landis",
    "in_exile_since"     => "1921-04-15",
    "end_of_exile"       => "1928-05-18",
]);

echo "Inserted 1918 IWW Chicago trial + 1921-1928 USSR exile case for Bill Haywood\n";
'

echo
echo "Bill Haywood update complete."
