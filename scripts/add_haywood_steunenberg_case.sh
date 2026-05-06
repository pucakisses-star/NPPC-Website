#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_haywood_steunenberg_case.sh
#
# Adds Bill Haywood's 1906-1907 Steunenberg-assassination pretrial
# detention as a second case row, alongside his existing 1918-1919
# IWW Espionage Act case.
#
# History: On December 30, 1905, former Idaho Governor Frank
# Steunenberg was killed by a bomb at his Caldwell, Idaho home.
# Pinkerton detective James McParland obtained a confession from Harry
# Orchard naming Western Federation of Miners leaders Bill Haywood,
# Charles Moyer, and George Pettibone as having ordered the
# assassination. On February 17, 1906, Pinkerton agents seized
# Haywood, Moyer, and Pettibone in Denver in violation of normal
# extradition procedures and put them on a special train to Boise.
# Haywood was held in the Ada County Jail without bail. While in
# jail in 1906 he was nominated by the Socialist Party of Colorado
# as their candidate for governor (he lost).
#
# Trial began May 9, 1907 in Boise; defense by Clarence Darrow. Steve
# Adams (the prosecution's hoped-for corroborating witness, also in
# this database) recanted his McParland-coerced confession via a note
# passed by his wife, gutting the prosecution's case. The jury
# deliberated nine hours and acquitted Haywood on July 29, 1907.
#
# Source: Bill Haywood, Wikipedia; Library of Congress Chronicling
# America Haywood Trial guide; The Bill Haywood Trial - Clarence
# Darrow Digital Collection (University of Minnesota Law School).
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Bill Haywood")->first();
if (!$p) {
    echo "ERROR: Bill Haywood not found\n";
    exit(1);
}

// Locate or create the Ada County Jail institution
$inst = App\Models\Institution::firstOrCreate(
    ["name" => "Ada County Jail"],
    ["city" => "Boise", "state" => "Idaho"]
);

// Idempotent: skip insert if a 1906-07 Steunenberg case already exists
$existing = App\Models\PrisonerCase::where("prisoner_id", $p->id)
    ->where(function ($q) {
        $q->where("charges", "like", "%Steunenberg%")
          ->orWhere("charges", "like", "%Ada County%");
    })
    ->first();

if ($existing) {
    echo "Steunenberg case already exists for Bill Haywood (id={$existing->id}); skipping.\n";
    exit(0);
}

App\Models\PrisonerCase::create([
    "prisoner_id"        => $p->id,
    "institution_id"     => $inst->id,
    "charges"            => "Idaho state conspiracy to commit murder of former Idaho Governor Frank Steunenberg, killed by a bomb at his Caldwell home December 30, 1905. Prosecution case rested on the confession of Harry Orchard, obtained by Pinkerton detective James McParland, naming Haywood, Charles Moyer, and George Pettibone of the Western Federation of Miners as having ordered the assassination. Pinkerton agents seized all three in Denver on February 17, 1906 and rendered them to Idaho without normal extradition proceedings.",
    "arrest_date"        => "1906-02-17",
    "incarceration_date" => "1906-02-17",
    "release_date"       => "1907-07-29",
    "sentence"           => "Held without bail in the Ada County Jail, Boise, Idaho from Feb 17, 1906 to July 29, 1907 (528 days pretrial). Trial opened May 9, 1907, defended by Clarence Darrow. After Steve Adams recanted his McParland-coerced corroborating confession (via a note passed by his wife), the prosecution case collapsed; jury deliberated nine hours and acquitted on July 29, 1907.",
    "convicted"          => "No - acquitted by jury, Ada County District Court, Boise, July 29, 1907",
    "judge"              => "Fremont Wood",
    "prosecutor"         => "James Hawley and Senator William Borah",
]);
echo "Inserted Bill Haywood 1906-1907 Steunenberg pretrial detention case (528 days at Ada County Jail).\n";
'

echo
echo "Bill Haywood Steunenberg case add complete."
