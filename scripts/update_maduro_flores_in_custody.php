<?php

declare(strict_types=1);

/**
 * Update Maduro & Cilia Flores to reflect their actual capture and
 * U.S. custody (the prior in_custody=false flip was based on stale
 * pre-2026 facts).
 *
 * Events:
 *   Jan 3, 2026 — Captured by U.S. special forces in Caracas during
 *                 Operation Absolute Resolve. Transferred to USS Iwo
 *                 Jima, flown to Stewart Air National Guard Base, then
 *                 transported to Metropolitan Detention Center,
 *                 Brooklyn (BOP MDC Brooklyn).
 *   Jan 5, 2026 — Both arraigned before Judge Alvin Hellerstein, SDNY.
 *                 Pleaded not guilty to narco-terrorism / cocaine
 *                 importation conspiracy charges.
 *
 * Run on production:
 *   cd /var/www/NPPC-Website && php scripts/update_maduro_flores_in_custody.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;

$mdc = Institution::firstOrCreate(
    ['name' => 'Metropolitan Detention Center, Brooklyn (MDC Brooklyn)'],
    ['city' => 'Brooklyn', 'state' => 'New York']
);

$arrestDate = '2026-01-03';
$arraignDate = '2026-01-05';

$maduroCharges = "Sealed superseding indictment unsealed March 26, 2020, U.S. v. Maduro Moros et al. (S.D.N.Y.). Counts include 21 U.S.C. § 960a narco-terrorism conspiracy; 21 U.S.C. §§ 963, 959(a), 960(b)(1)(B) conspiracy to import 5+ kg of cocaine into the United States; 18 U.S.C. § 924(c) possession of machineguns and destructive devices in furtherance of drug trafficking; 18 U.S.C. § 924(o) conspiracy to possess machineguns. The DOJ alleges Maduro co-led the Cartel de los Soles together with senior Venezuelan officials and dissident FARC commanders to traffic cocaine into the United States. Maximum possible penalty: life imprisonment. Captured January 3, 2026 by U.S. special forces in Caracas during Operation Absolute Resolve; transferred to USS Iwo Jima, flown to Stewart Air National Guard Base, and detained at MDC Brooklyn. Arraigned January 5, 2026 before Judge Alvin Hellerstein; pleaded not guilty.";

$ciliaCharges = "U.S. v. Maduro Moros et al. (S.D.N.Y.) — narco-terrorism and cocaine-trafficking charges. Indictment alleges that Cilia Flores brokered a 2007 meeting between a large-scale drug trafficker and the director of Venezuela's National Anti-Drug Office and accepted hundreds of thousands of dollars in bribes. Captured January 3, 2026 alongside her husband during Operation Absolute Resolve in Caracas. Arraigned January 5, 2026 before Judge Alvin Hellerstein at SDNY; pleaded not guilty. Counsel reported she sustained fractured ribs during the capture. Detained at MDC Brooklyn.";

// ---- Maduro ----
$maduro = Prisoner::where('name', 'Nicolas Maduro Moros')
    ->orWhere('name', 'Nicolás Maduro Moros')
    ->orWhere('name', 'like', '%Maduro Moros%')
    ->first();

if ($maduro) {
    $maduro->in_custody = true;
    $maduro->released = false;
    $maduro->state = 'New York';
    $maduro->save();

    $case = $maduro->cases()->orderBy('created_at')->first();
    if (! $case) {
        $case = new PrisonerCase(['prisoner_id' => $maduro->id]);
    }
    $case->institution_id = $mdc->id;
    $case->charges = $maduroCharges;
    $case->arrest_date = $arrestDate;
    $case->incarceration_date = $arrestDate;
    $case->release_date = null;
    $case->convicted = 'Pending — pleaded not guilty Jan 5, 2026';
    $case->sentence = 'Pending';
    $case->judge = 'Hon. Alvin K. Hellerstein (S.D.N.Y.)';
    $case->save();
    echo "Maduro: in_custody={$maduro->in_custody}, arrest_date={$case->arrest_date}, institution={$mdc->name}\n";
} else {
    echo "Maduro: not found\n";
}

// ---- Cilia Flores ----
$cilia = Prisoner::where('name', 'Cilia Adela Flores de Maduro')
    ->orWhere('name', 'Cilia Flores')
    ->orWhere('aka', 'like', '%Cilia Flores%')
    ->first();

if ($cilia) {
    $cilia->in_custody = true;
    $cilia->released = false;
    $cilia->state = 'New York';
    if (! $cilia->birthdate) $cilia->birthdate = '1956-10-15';
    $cilia->save();

    $case = $cilia->cases()->orderBy('created_at')->first();
    if (! $case) {
        $case = new PrisonerCase(['prisoner_id' => $cilia->id]);
    }
    $case->institution_id = $mdc->id;
    $case->charges = $ciliaCharges;
    $case->arrest_date = $arrestDate;
    $case->incarceration_date = $arrestDate;
    $case->release_date = null;
    $case->convicted = 'Pending — pleaded not guilty Jan 5, 2026';
    $case->sentence = 'Pending';
    $case->judge = 'Hon. Alvin K. Hellerstein (S.D.N.Y.)';
    $case->save();
    echo "Cilia Flores: in_custody={$cilia->in_custody}, arrest_date={$case->arrest_date}, institution={$mdc->name}\n";
} else {
    echo "Cilia Flores: not found\n";
}

echo "Done.\n";
