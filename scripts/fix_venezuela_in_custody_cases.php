<?php

declare(strict_types=1);

/**
 * Fix custody-status data for the seven Venezuela-related prisoners
 * who actually were/are in U.S. custody:
 *
 *   - Cliver Alcalá Cordero: flag incorrectly said released=true; he
 *     is serving a 262-month sentence imposed March 5, 2024 with no
 *     scheduled release until ~2042.
 *   - Alex Saab: institution was wrongly listed as "Albany
 *     Penitentiary"; he was extradited Oct 16, 2021 and held at FDC
 *     Miami until Dec 20, 2023 when Biden granted clemency in the
 *     U.S.-Venezuela prisoner swap.
 *   - Hugo Carvajal Barrios: sentence note updated to reflect the
 *     50-year mandatory minimum after his June 25, 2025 guilty plea
 *     (sentencing still pending as of Dec 2025).
 *
 * Maduro, Cilia Flores, Campo Flores, and Flores de Freitas already
 * have accurate case data from prior PRs.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;
use App\Models\Prisoner;

// ---- Cliver Alcalá Cordero: still in custody, not released ----
$alcala = Prisoner::where('name', 'Cliver Antonio Alcala Cordero')->first();
if ($alcala) {
    $alcala->in_custody = true;
    $alcala->released = false;
    $alcala->save();
    $case = $alcala->cases()->first();
    if ($case) {
        $case->release_date = null;
        $case->convicted = 'Yes — guilty plea August 2023';
        $case->sentenced_date = '2024-03-05';
        $case->sentence = '262 months federal prison (sentenced March 5, 2024); projected release ~2042';
        $case->judge = 'Hon. Alvin K. Hellerstein (S.D.N.Y.)';
        $case->save();
        echo "Alcalá: in_custody=true, sentenced 262 months, no release date.\n";
    }
} else {
    echo "Alcalá: not found\n";
}

// ---- Alex Saab: corrected institution + clemency context ----
$saab = Prisoner::where('name', 'Alex Saab')->first();
if ($saab) {
    $saab->in_custody = false;
    $saab->released = true;
    $saab->save();
    $inst = Institution::firstOrCreate(
        ['name' => 'Federal Detention Center, Miami (FDC Miami)'],
        ['city' => 'Miami', 'state' => 'Florida']
    );
    $case = $saab->cases()->first();
    if ($case) {
        $case->institution_id = $inst->id;
        $case->arrest_date = '2020-06-12'; // Cape Verde airport detention
        $case->incarceration_date = '2021-10-16'; // Extradition to U.S. (FDC Miami)
        $case->release_date = '2023-12-20'; // Biden clemency / prisoner swap
        $case->convicted = 'No — released Dec 20, 2023 via Presidential clemency in U.S.-Venezuela prisoner swap';
        $case->sentence = 'No sentence imposed. Detained ~3.5 years total: ~16 months in Cape Verde (June 2020 - Oct 2021) before extradition to FDC Miami (Oct 16, 2021 - Dec 20, 2023). Released to Venezuela in exchange for 10 Americans.';
        $case->charges = 'Money laundering and conspiracy to commit money laundering (18 U.S.C. §§ 1956, 1957) — 8 counts in S.D. Fla. Alleged $350M moved out of Venezuela through U.S.-controlled accounts via fraudulent affordable-housing contracts. Pleaded not guilty; case ended via clemency.';
        $case->save();
        echo "Saab: institution -> FDC Miami; incarc 2021-10-16, released 2023-12-20.\n";
    }
} else {
    echo "Saab: not found\n";
}

// ---- Hugo Carvajal: update sentence note for mandatory minimum ----
$carvajal = Prisoner::where('name', 'Hugo Armando Carvajal Barrios')->first();
if ($carvajal) {
    $case = $carvajal->cases()->first();
    if ($case) {
        $case->convicted = 'Yes — guilty plea June 25, 2025';
        $case->sentence = 'Pleaded guilty to 4 counts (narcoterrorism conspiracy, conspiracy to import cocaine, machinegun possession in furtherance of drug trafficking, conspiracy to possess machineguns). Mandatory minimum 50 years. Sentencing was scheduled for Oct 29, 2025 and remains pending as of Dec 2025.';
        $case->judge = 'Hon. Alvin K. Hellerstein (S.D.N.Y.)';
        $case->save();
        echo "Carvajal: sentence note updated with 50-year mandatory minimum.\n";
    }
} else {
    echo "Carvajal: not found\n";
}

echo "\nDone.\n";
