<?php

declare(strict_types=1);

/**
 * Run on production:
 *   cd /var/www/NPPC-Website && php scripts/update_nicolas_maduro_bio.php
 *
 * Expands Nicolás Maduro's bio with biographical and political-career
 * details that were missing from the original TPP-derived record, and
 * fills in birthdate plus a few previously-blank case fields.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$prisoner = Prisoner::where('name', 'Nicolas Maduro Moros')
    ->orWhere('name', 'Nicolás Maduro Moros')
    ->orWhere(function ($q) {
        $q->where('first_name', 'Nicolas')->where('last_name', 'Moros');
    })
    ->first();

if (! $prisoner) {
    fwrite(STDERR, "Could not find Nicolas Maduro Moros prisoner record.\n");
    exit(1);
}

echo "Updating prisoner: {$prisoner->name} (id={$prisoner->id})\n";

$description = <<<'TXT'
Nicolás Maduro Moros (born November 23, 1962, in Caracas) is the de facto president of Venezuela. A former Caracas Metro bus driver and metro workers' union organizer, he entered politics in the late 1990s as a Chavista deputy, served as president of the National Assembly in 2005–2006, as foreign minister from 2006 to 2013, and as vice president from October 2012. He assumed the acting presidency on March 5, 2013 after Hugo Chávez's death from cancer and narrowly won an April 14, 2013 special election against Henrique Capriles.

Maduro's May 2018 re-election was widely denounced as fraudulent and most major opposition parties boycotted. In January 2019, opposition leader Juan Guaidó declared himself interim president and was recognized by the United States and dozens of other governments. In the July 28, 2024 election, the National Electoral Council declared Maduro the winner without releasing voting-machine tally sheets; opposition campaigns published more than 80 percent of precinct-level "acta" sheets showing challenger Edmundo González Urrutia winning by roughly two to one. Maduro was inaugurated for a third term on January 10, 2025 amid widespread international non-recognition.

He was indicted March 26, 2020 by the U.S. Attorney's Office for the Southern District of New York on narco-terrorism, international cocaine-importation conspiracy, and weapons charges, in a sealed superseding indictment unsealed alongside charges against fourteen other defendants including former vice president Diosdado Cabello, retired major general Cliver Alcalá Cordero, and former military intelligence chief Hugo Carvajal. The DOJ alleged that Maduro co-led the Cartel de los Soles together with senior Venezuelan officials and dissident FARC commanders to traffic cocaine into the United States.

The U.S. State Department's reward for information leading to Maduro's arrest, initially $15 million, was raised to $25 million in January 2025 and to $50 million in August 2025 — the largest narcotics-related bounty ever announced by the United States. He has never been in U.S. custody. He is the subject of OFAC SDN sanctions and is married to Cilia Flores, herself a co-defendant in related U.S. proceedings tied to the 2015 "narco-sobrinos" case involving her nephews.
TXT;

$prisoner->description = $description;
$prisoner->birthdate = '1962-11-23';
if (! $prisoner->aka) {
    $prisoner->aka = 'Nicolás Maduro';
}
if (! $prisoner->affiliation || ! in_array('Bolivarian Government of Venezuela', $prisoner->affiliation, true)) {
    $prisoner->affiliation = array_values(array_unique(array_merge(
        $prisoner->affiliation ?? [],
        ['United Socialist Party of Venezuela (PSUV)', 'Cartel de los Soles', 'Bolivarian Government of Venezuela']
    )));
}
$prisoner->save();
echo "  - description, birthdate, aka, affiliation updated\n";

// --- Case fill-in ---------------------------------------------------------

$case = $prisoner->cases()->first();
if (! $case) {
    echo "  - no case row found; skipping case update\n";
} else {
    $case->indicted = $case->indicted ?: '2020-03-26';
    if (! $case->prosecutor) {
        $case->prosecutor = 'U.S. Attorney\'s Office for the Southern District of New York (Geoffrey S. Berman, U.S. Attorney at filing)';
    }
    $charges = 'Sealed superseding indictment unsealed March 26, 2020, U.S. v. Maduro Moros et al. (S.D.N.Y.). Counts: 21 U.S.C. § 960a narco-terrorism conspiracy; 21 U.S.C. §§ 963, 959(a), 960(b)(1)(B) conspiracy to import cocaine; 18 U.S.C. § 924(c) possession of machineguns and destructive devices in furtherance of drug trafficking; 18 U.S.C. § 924(o) conspiracy to possess machineguns. Maximum possible penalty life imprisonment.';
    if (! $case->charges || strlen($case->charges) < strlen($charges)) {
        $case->charges = $charges;
    }
    $case->convicted = $case->convicted ?: 'Pending — never apprehended';
    $case->sentence = $case->sentence ?: 'Pending — never apprehended';
    $case->save();
    echo "  - case (id={$case->id}) indicted/prosecutor/charges/status filled\n";
}

echo "\nDone.\n";
