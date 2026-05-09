<?php

declare(strict_types=1);

/**
 * Set death_date for Tom Lewis (Baltimore 4 / Catonsville 9 Catholic
 * peace activist). Born March 17, 1940; died April 4, 2008 in
 * Worcester, MA. Idempotent.
 *
 * Note: there is also a "Thomas Lewis" record (slug thomas-lewis)
 * which already has the correct death date and is the same person —
 * those two records are duplicates and may want to be merged later.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$p = Prisoner::where('slug', 'tom-lewis')->first()
    ?? Prisoner::where('name', 'Tom Lewis')->first();

if (! $p) {
    echo "Tom Lewis not found.\n";
    exit(1);
}

if ($p->death_date) {
    echo "Already set: {$p->name} death_date = " . $p->death_date->format('Y-m-d') . "\n";
    exit(0);
}

$p->death_date = '2008-04-04';
$p->save();
echo "Set: {$p->name} death_date = 2008-04-04\n";
