<?php

declare(strict_types=1);

/**
 * Tighten Annell Ponder's Winona, Mississippi case row.
 *
 * Confirmed via SNCC Digital Gateway and Civil Rights Teaching:
 * Ponder, Hamer, June Johnson, Lawrence Guyot, Euvester Simpson,
 * James West, and Rosemary Freeman were arrested at the Winona
 * Trailways bus station on June 9, 1963 and held in Montgomery
 * County jail for approximately four days before SCLC bailed them
 * out (~June 13). All seven were severely beaten in custody.
 *
 * Run on production:
 *   cd /var/www/NPPC-Website && php scripts/update_annell_ponder_winona_dates.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;

$ponder = Prisoner::where('name', 'Annell Ponder')->first();
if (! $ponder) {
    echo "Annell Ponder: not found\n";
    exit(1);
}

echo "Found Annell Ponder (id={$ponder->id}); existing cases: " . $ponder->cases()->count() . "\n";

// Try in order: case with the right arrest_date, then any existing case,
// then create a fresh one.
$case = $ponder->cases()->where('arrest_date', '1963-06-09')->first()
    ?? $ponder->cases()->orderBy('created_at')->first();

if (! $case) {
    echo "  No existing case — creating a fresh Winona case row.\n";
    $inst = Institution::firstOrCreate(
        ['name' => 'Montgomery County Jail (Winona, MS) — June 1963 SNCC beating'],
        ['city' => 'Winona', 'state' => 'Mississippi']
    );
    $case = new PrisonerCase([
        'prisoner_id' => $ponder->id,
        'institution_id' => $inst->id,
        'charges' => 'Disorderly conduct / refusing to leave segregated bus station — Winona, MS, June 9, 1963 (federal civil-rights prosecution against the police later resulted in acquittals).',
    ]);
}

$case->arrest_date = '1963-06-09';
$case->incarceration_date = '1963-06-09';
$case->release_date = '1963-06-13';
$case->sentence = 'Approximately 4 days in Montgomery County jail (Winona, MS); SCLC posted bail. Severely beaten in custody by Highway Patrol officers and prisoners under their direction.';
$case->save();
$case->refresh();

echo sprintf(
    "Updated Annell Ponder case: arrest=%s, release=%s, days=%s\n",
    $case->arrest_date instanceof \DateTimeInterface ? $case->arrest_date->format('Y-m-d') : (string) $case->arrest_date,
    $case->release_date instanceof \DateTimeInterface ? $case->release_date->format('Y-m-d') : (string) $case->release_date,
    var_export($case->imprisoned_for_days, true)
);
