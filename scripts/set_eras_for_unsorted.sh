#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/set_eras_for_unsorted.sh
#
# Sets `era` on every prisoner that was previously falling to the bottom
# of the list because of an unset/unrecognized era value. Uses the era
# vocabulary recognized by app/Console/Commands/ResortPrisonersByEra.php
# (decade strings like "1910s", or "Civil War"). After updating eras,
# re-runs prisoners:resort-by-era so sort_order reflects the new mapping.
set -e

php artisan tinker --execute='
use App\Models\Prisoner;

$eras = [
    // 2020s — George Floyd uprising, Palestine solidarity, Trump-era ICE
    "Alireza Doroudi"               => "2020s",
    "Leqaa Kordia"                  => "2020s",
    "Angel Espinosa-Villegas"       => "2020s",
    "Casey Goonan"                  => "2020s",
    "Cody Nowlin"                   => "2020s",
    "Renea Goddard"                 => "2020s",
    "Hridindu Roychowdhury"         => "2020s",
    "Ruby Montoya"                  => "2020s",
    "Mujera Benjamin Lunga’ho"      => "2020s",
    "Alvin Joseph"                  => "2020s",
    "Anthony Smith"                 => "2020s",
    "Christopher Tindal"            => "2020s",
    "Khalif Miller"                 => "2020s",
    "Richard Hunsinger"             => "2020s",
    "Tyre Means"                    => "2020s",
    "John Wade"                     => "2020s",
    "Ellie Brett"                   => "2020s",
    "Howard Nall"                   => "2020s",
    "Hassan Muhammad"               => "2020s",
    "Adam Walton"                   => "2020s",
    "Dajon Lengyel"                 => "2020s",
    "Jacob Gaines"                  => "2020s",
    "Isaiah Willoughby"             => "2020s",
    "Shawn Sutton"                  => "2020s",

    // 2010s — Kings Bay Plowshares, Puerto Rico, post-9/11 prosecutions still active
    "Elizabeth McAlister"           => "2010s",
    "Nina Droz Franco"              => "2010s",
    "Mehrdad Moein Ansari"          => "2010s",
    "Kwame Shakur"                  => "2010s",

    // 2000s
    "Joseph Shine White Stewart"    => "2000s",
    "Andrew Mickel"                 => "2000s",
    "Tarik Shah"                    => "2000s",

    // 1990s
    "Wen Ho Lee"                    => "1990s",
    "Daniel Sicken"                 => "1990s",
    "Kathleen Rumpf"                => "1990s",
    "Sundiata Jawanza"              => "1990s",
    "Keith LaMar"                   => "1990s",
    "Siddique Abdullah Hasan"       => "1990s",
    "Carl Johnson"                  => "1990s",
    "Timothy Reed"                  => "1990s",
    "Shaka Shakur"                  => "1990s",

    // 1980s
    "Abdullah Malik Ka’bah"         => "1980s",
    "Georges Ibrahim Abdallah"      => "1980s",
    "Iya Fulani Sunni-Ali"          => "1980s",
    "Abdul Olugbala Shakur"         => "1980s",
    "Christopher Trotter"           => "1980s",
    "Kuwasi Balagoon"               => "1980s",
    "Michael Kimble"                => "1980s",

    // 1970s — Williamsburg 4, Soledad Brothers, BLA, Angola 3, Panther 21
    "Safiya Bukhari"                => "1970s",
    "Dawud Abdur Rahman"            => "1970s",
    "Oscar Washington"              => "1970s",
    "Salih Ali Abdullah"            => "1970s",
    "Shuaib Abdur Raheem"           => "1970s",
    "Yusuf A. Mussadiq"             => "1970s",
    "Fleeta Drumgo"                 => "1970s",
    "John Cluchette"                => "1970s",
    "Ashanti Alston"                => "1970s",
    "Herman Wallace"                => "1970s",
    "James Johnson"                 => "1970s",
    "Masai Ehehosi"                 => "1970s",
    "Robert Hillary King"           => "1970s",
    "Robert Hugh Wilson"            => "1970s",
    "Teddy Jah Heath"               => "1970s",

    // 1960s — Panther 21, Hampton/Clark raid, Soledad/Jackson, Wilkinson, Scales, Barenblatt
    "Afeni Shakur"                  => "1960s",
    "Mark Clark"                    => "1960s",
    "Robert Webb"                   => "1960s",
    "Herman Ferguson"               => "1960s",
    "George Jackson"                => "1960s",
    "Frank Wilkinson"               => "1960s",
    "Junius Scales"                 => "1960s",
    "Lloyd Barenblatt"              => "1960s",

    // 1950s — Smith Act, Foley Square Second String, Hollywood Ten, JAFRC, Rosenbergs
    "Saul Wellman"                  => "1950s",
    "Albert Lannon"                 => "1950s",
    "Alexander Bittelman"           => "1950s",
    "Alexander Trachtenberg"        => "1950s",
    "Arnold Johnson"                => "1950s",
    "Betty Gannett"                 => "1950s",
    "Dashiell Hammett"              => "1950s",
    "Elizabeth Gurley Flynn"        => "1950s",
    "George Blake Charney"          => "1950s",
    "Jacob Mindel"                  => "1950s",
    "Louis Weinstock"               => "1950s",
    "Maurice Braverman"             => "1950s",
    "Pettis Perry"                  => "1950s",
    "Steve Nelson"                  => "1950s",
    "V. J. Jerome"                  => "1950s",
    "William W. Weinstone"          => "1950s",
    "Ethel Rosenberg"               => "1950s",
    "Julius Rosenberg"              => "1950s",
    "Adrian Scott"                  => "1950s",
    "Albert Maltz"                  => "1950s",
    "Alvah Bessie"                  => "1950s",
    "Dalton Trumbo"                 => "1950s",
    "Edward Dmytryk"                => "1950s",
    "Edward K. Barsky"              => "1950s",
    "Helen R. Bryan"                => "1950s",
    "Herbert Biberman"              => "1950s",
    "Howard Fast"                   => "1950s",
    "John Howard Lawson"            => "1950s",
    "Lester Cole"                   => "1950s",
    "Lyman R. Bradley"              => "1950s",
    "Ring Lardner Jr."              => "1950s",
    "Samuel Ornitz"                 => "1950s",

    // 1940s — Iva Toguri / Tokyo Rose, first HUAC contempt
    "Iva Toguri D’Aquino"           => "1940s",
    "Leon Josephson"                => "1940s",

    // 1910s — Espionage Act, Comstock-era reproductive rights, Lawrence textile, Magón
    "Margaret Sanger"               => "1910s",
    "A. J. Muste"                   => "1910s",
    "Bill Haywood"                  => "1910s",
    "Jacob Frohwerk"                => "1910s",
    "Louise Olivereau"              => "1910s",
    "Mollie Steimer"                => "1910s",
    "Ricardo Flores Magón"          => "1910s",

    // Civil War
    "James Russell Hallam"          => "Civil War",
];

$updated = 0;
$missing = [];
foreach ($eras as $name => $era) {
    $p = Prisoner::where("name", $name)->first();
    if (! $p) { $missing[] = $name; continue; }
    if ($p->era !== $era) {
        $p->era = $era;
        $p->saveQuietly();
        $updated++;
    }
}

echo "Updated era on {$updated} prisoners.\n";
if (count($missing)) {
    echo "Could not find: \n  - " . implode("\n  - ", $missing) . "\n";
}
'

echo
echo "Now resorting sort_order..."
php artisan prisoners:resort-by-era

echo
echo "Era assignment + resort complete."
