<?php

declare(strict_types=1);

/**
 * Patch Christopher W. McIntosh from BOP locator data:
 *   Register: 30512-013
 *   Race: White
 *   Sex: Male
 *   Released: 2012-11-23
 *
 * Existing case has charges (Federal arson — Jan 2003 ELF/ALF-claimed
 * Seattle McDonalds rooftop fire) and arrest_date 2005-04-05 from
 * the recovered HTML import; we add the missing release date,
 * recompute imprisoned_for_days from the corrected window
 * (ignoring the bogus 7718 from the import), set incarceration_date
 * to match arrest_date, and flip the released flags.
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use Carbon\Carbon;

$p = Prisoner::whereRaw('LOWER(name) IN (?, ?, ?)', [
    'christopher w. mcintosh',
    'christopher w mcintosh',
    'christopher mcintosh',
])->first();
if (! $p) {
    echo "Christopher W. McIntosh not found.\n";
    exit(1);
}

// Prisoner-level patches (only fill blanks)
if (empty(trim((string) $p->inmate_number))) $p->inmate_number = '30512-013';
if (empty(trim((string) $p->race)))           $p->race          = 'White';
if (empty(trim((string) $p->gender)))         $p->gender        = 'Male';
$p->in_custody = false;
$p->released   = true;
$p->save();

// Case-level patches
$case = $p->cases()->orderBy('created_at')->first();
if (! $case) {
    echo "No case row to patch. Aborting.\n";
    exit(1);
}

$case->release_date        = '2012-11-23';
$case->incarceration_date  = '2005-04-05'; // continuous federal pretrial -> sentence
$startCarbon = Carbon::parse('2005-04-05');
$endCarbon   = Carbon::parse('2012-11-23');
$case->imprisoned_for_days = (int) $startCarbon->diffInDays($endCarbon);
$case->save();

echo sprintf(
    "[fix] %s :: BOP #30512-013, released 2012-11-23, served %d days (~%.1f years).\n",
    $p->name, $case->imprisoned_for_days, $case->imprisoned_for_days / 365
);
echo "Done.\n";
