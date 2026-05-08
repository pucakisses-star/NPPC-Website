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

use App\Models\Prisoner;

$ponder = Prisoner::where('name', 'Annell Ponder')->first();
if (! $ponder) {
    echo "Annell Ponder: not found\n";
    exit(1);
}

$case = $ponder->cases()
    ->where('arrest_date', '1963-06-09')
    ->orWhere(function ($q) use ($ponder) {
        $q->where('prisoner_id', $ponder->id)
          ->whereNull('arrest_date');
    })
    ->orderBy('created_at')
    ->first();

if (! $case) {
    echo "Annell Ponder: no Winona case found\n";
    exit(1);
}

$case->arrest_date = '1963-06-09';
$case->incarceration_date = '1963-06-09';
$case->release_date = '1963-06-13';
$case->sentence = 'Approximately 4 days in Montgomery County jail (Winona, MS); SCLC posted bail. Severely beaten in custody by Highway Patrol officers and prisoners under their direction.';
$case->save();

// Carbon-recompute imprisoned_for_days via boot hook
$case->refresh();
echo sprintf("Updated Annell Ponder: arrest=%s, release=%s, days=%d\n",
    $case->arrest_date->toDateString(),
    $case->release_date->toDateString(),
    $case->imprisoned_for_days
);
