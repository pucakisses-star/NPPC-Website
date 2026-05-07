<?php

declare(strict_types=1);

/**
 * Compare every named participant from Art Laffin's "Chronology of
 * Plowshares Disarmament Actions" (Kings Bay Plowshares 7 site, plus
 * well-attested later actions) against the prisoners table and report
 * which names have no matching record.
 *
 * Match is liberal — first + last token, case-insensitive, ignoring
 * common punctuation and titles. This reports candidates only and
 * never writes to the database.
 *
 * Usage:
 *   php scripts/find_missing_plowshares_prisoners.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

/**
 * Action -> participant list. Sourced from Art Laffin's chronology
 * (https://kingsbayplowshares7.org/plowshares-history/) plus widely-
 * documented later actions (Pax Christi, Prince of Peace, Disarm Now,
 * Transform Now, Kings Bay 7, etc.). Foreign-action participants are
 * tagged so the user can decide whether to include non-US prosecutions.
 */
$chronology = [
    '1980-09-09 Plowshares Eight (King of Prussia, PA)' => [
        'Daniel Berrigan','Philip Berrigan','Dean Hammer','Elmer Maas',
        'Carl Kabat','Anne Montgomery','Molly Rush','John Schuchardt',
    ],
    '1980-12-13 Plowshares Number Two (Groton, CT)' => [
        'Peter DeMott',
    ],
    '1982-07-04 Trident Nein (Groton, CT)' => [
        'Judy Beaumont','Anne Montgomery','James Cunningham','George Veasey',
        'Tim Quinn','Anne Bennis','Bill Hartman','Vincent Kay','Art Laffin',
    ],
    '1982-11-14 Plowshares Number Four (Groton, CT)' => [
        'John Grady','Ellen Grady','Peter DeMott','Jean Holladay',
        'Roger Ludwig','Elmer Maas','Marcia Timmel',
    ],
    '1983-07-14 AVCO Plowshares (Wilmington, MA)' => [
        'Agnes Bauerlein','Macy Morse','Mary Lyons','Frank Panopoulos',
        'Jean Holladay','John Pendleton','John Schuchardt',
    ],
    '1983-11-24 Griffiss Plowshares (Rome, NY)' => [
        'Jackie Allen','Clare Grady','Dean Hammer','Elizabeth McAlister',
        'Vern Rossman','Kathleen Rumpf','Karl Smith',
    ],
    '1983-12-04 Plowshares Number Seven (Schwabisch-Gmund, West Germany)' => [
        'Carl Kabat','Herwig Jantschik','Wolfgang Sternstein','Karin Vix',
    ],
    '1984-04-22 Pershing Plowshares (Orlando, FL)' => [
        'Per Herngren','Paul Magno','Todd Kaplan','Tim Lietzke',
        'Anne Montgomery','Patrick O\'Neill','Jim Perkins','Christin Schmidt',
    ],
    '1984-08-10 Sperry Software Pair (Eagan, MN)' => [
        'John LaForge','Barbara Katt',
    ],
    '1984-10-01 Trident II Plowshares (North Kingston, RI)' => [
        'William Boston','Jean Holladay','Frank Panopoulos','John Pendleton','Leo Schiff',
    ],
    '1984-11-12 Silo Pruning Hooks (Whiteman AFB, MO)' => [
        'Carl Kabat','Paul Kabat','Larry Cloud Morgan','Helen Woodson',
    ],
    '1985-02-19 Plowshares Number Twelve (Whiteman AFB, MO)' => [
        'Martin Holladay',
    ],
    '1985-04-18 Trident II Pruning Hooks (North Kingston, RI)' => [
        'Greg Boertje','John Heid','Roger Ludwig','Sheila Parks',
        'Suzanne Schmidt','George Veasey',
    ],
    '1985-05-28 Michigan ELF Disarmament (MI)' => [
        'Tom Hastings',
    ],
    '1985-07-16 Pantex Disarmament (Amarillo, TX)' => [
        'Richard Miller',
    ],
    '1985-08-14 Wisconsin ELF Disarmament (WI)' => [
        'Jeff Leys',
    ],
    '1985-09-27 Martin Marietta MX Witness (Denver, CO)' => [
        'Al Zook','Mary Sprunger-Froese','Marie Nord',
    ],
    '1986-03-28 Silo Plowshares (Whiteman AFB, MO)' => [
        'Darla Bradley','Larry Morlan','Jean Gump','Ken Rippetoe','John Volpe',
    ],
    '1986-12-12 Pershing to Plowshares (Schwabisch-Gmund, West Germany)' => [
        'Heike Huschauer','Suzanne Mauch-Fritz','Wolfgang Sternstein','Stellan Vinthagen',
    ],
    '1987-01-06 Epiphany Plowshares (Horsham, PA)' => [
        'Greg Boertje','Dexter Lanctot','Thomas McGann','Lin Romano',
    ],
    '1987-04-17 Paupers Plowshares (Warminster, PA)' => [
        'Pat Sieber','Rick Sieber',
    ],
    '1987-06-02 White Rose (Vandenberg AFB, CA)' => [
        'Katya Komisaruk',
    ],
    '1987-08-05 Transfiguration Plowshares West (Whiteman AFB, MO)' => [
        'Jerry Ebner','Joe Gump','Helen Woodson',
    ],
    '1987-08-06 Transfiguration Plowshares East (South Weymouth NAS, MA)' => [
        'Margaret Brodhead','Dan Ethier','Tom Lewis',
    ],
    '1987-08-16 Harmonic Disarmament for Life (Clam Lake, WI)' => [
        'George Ostensen','Helen Woodson',
    ],
    '1988-04-03 Nuclear Navy Plowshares (USS Iowa, Norfolk, VA)' => [
        'Philip Berrigan','Andrew Lawrence','Margaret McKenna','Greg Boertje',
    ],
    '1988-06-26 Kairos Plowshares (Groton, CT)' => [
        'Kathleen Maire','Jack Marth','Anne Montgomery','Christine Mulready',
    ],
    '1988-08-01 Kairos Plowshares Too (North Kingston, RI)' => [
        'Kathleen Maire','Anne Montgomery',
    ],
    '1988-09-20 Credo Plowshares (Washington, DC)' => [
        'Marcia Timmel',
    ],
    '1989-09-04 Thames River Plowshares (New London, CT)' => [
        'Art Laffin','Kathy Boylan','Jackie Allen','Elmer Maas','Jim Reale',
    ],

    // Later actions (1990s-2018) — confidently attested
    '1991-01-01 ANZUS Plowshares (Sydney, Australia)' => [
        'Bill Streit','Sue Frankel-Streit','Moana Cole','Ciaron O\'Reilly',
    ],
    '1991-12-07 Aegis Plowshares (Bath, ME)' => [
        'Daniel Sicken','Tom Lewis-Borbely','Anne Bennis','Philip Berrigan',
    ],
    '1995-02-12 Jubilee Plowshares East (Bath Iron Works, ME)' => [
        'Philip Berrigan','Lynn Fredriksson','Kathleen Boylan','Lin Romano',
        'Bruce Friedrich','John Dear',
    ],
    '1995-05-13 Jubilee Plowshares West (Bangor, WA)' => [
        'John Dear','Philip Berrigan','Bruce Friedrich','Lynn Fredriksson',
    ],
    '1997-02-12 Prince of Peace Plowshares (Bath Iron Works, ME)' => [
        'Stephen Kelly','Philip Berrigan','Mark Colville','Steve Baggarly',
        'Susan Crane','Tom Lewis',
    ],
    '1998-05-17 Gods of Metal Plowshares (Andrews AFB, MD)' => [
        'Frank Cordaro','Daniel Berrigan','Stephen Kelly','Susan Crane',
    ],
    '1999-12-19 Plowshares vs. Depleted Uranium (UK)' => [
        'Susan van der Hijden','Sylvia Boyes','River',
    ],
    '2002-12-19 Riverside Plowshares (Riverside, CA)' => [
        'Daniel Sicken','Toby Mendez','Mary Carolyn Kennedy',
    ],
    '2003-02-03 Pitstop Plowshares (Shannon Airport, Ireland)' => [
        'Ciaron O\'Reilly','Damien Moran','Karen Fallon',
        'Deirdre Clancy','Nuin Dunlop',
    ],
    '2003-04-06 Riverside Plowshares (Bath Iron Works, ME)' => [
        'Stephen Kelly','Susan Crane','Anne Montgomery',
    ],
    '2009-11-02 Disarm Now Plowshares (Bangor, WA)' => [
        'Bill Bichsel','Anne Montgomery','Susan Crane','Lynne Greenwald','Stephen Kelly',
    ],
    '2012-07-28 Transform Now Plowshares (Oak Ridge, TN)' => [
        'Megan Rice','Michael Walli','Greg Boertje-Obed',
    ],
    '2018-04-04 Kings Bay Plowshares 7 (Kings Bay, GA)' => [
        'Elizabeth McAlister','Mark Colville','Clare Grady','Martha Hennessy',
        'Patrick O\'Neill','Carmen Trotta','Stephen Kelly',
    ],
];

// Build a lookup of existing prisoners by normalised "first last" key.
function normName(string $s): string
{
    $s = preg_replace('/[\.\,\(\)\']/u', '', $s) ?? $s;
    $s = preg_replace('/\b(jr|sr|ii|iii|iv|fr|rev|sr\.|sister|brother|dr)\b/iu', '', $s) ?? $s;
    $s = mb_strtolower(trim((string) $s));
    $s = preg_replace('/\s+/', ' ', $s) ?? $s;
    return trim((string) $s);
}

function nameKeys(string $full): array
{
    $n = normName($full);
    if ($n === '') {
        return [];
    }
    $parts = explode(' ', $n);
    if (count($parts) < 2) {
        return [$n];
    }
    $first = $parts[0];
    $last  = end($parts);
    return [
        "{$first} {$last}",
        $n,
    ];
}

$existing = [];
foreach (Prisoner::all(['id','name','first_name','last_name','aka']) as $p) {
    foreach (array_filter([$p->name, $p->aka, "{$p->first_name} {$p->last_name}"]) as $candidate) {
        foreach (nameKeys($candidate) as $key) {
            $existing[$key] = $p->name;
        }
    }
}

echo "Plowshares participants vs. prisoners table:\n\n";

$totalNames = 0;
$missingNames = [];
foreach ($chronology as $action => $names) {
    $missing = [];
    foreach ($names as $n) {
        $totalNames++;
        $found = false;
        foreach (nameKeys($n) as $key) {
            if (isset($existing[$key])) {
                $found = true;
                break;
            }
        }
        if (! $found) {
            $missing[] = $n;
            $missingNames[$n] = ($missingNames[$n] ?? []);
            $missingNames[$n][] = $action;
        }
    }
    if (! empty($missing)) {
        echo "  {$action}\n";
        foreach ($missing as $n) {
            echo "    - {$n}\n";
        }
    }
}

echo "\n";
echo 'Total participant entries scanned: ' . $totalNames . "\n";
echo 'Distinct missing names: ' . count($missingNames) . "\n";
echo "\nDistinct missing list (paste candidates for new prisoner records):\n";
foreach ($missingNames as $name => $actions) {
    echo "  - {$name}  [actions: " . count($actions) . "]\n";
}
