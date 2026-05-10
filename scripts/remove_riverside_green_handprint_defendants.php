<?php

declare(strict_types=1);

/**
 * Remove the six Riverside green-handprint defendants from the
 * 2022 Rise Up 4 Abortion Rights vandalism case. Per Rise Up 4
 * Abortion Rights statements, charges were dropped against some
 * and reduced from felony to misdemeanor against the rest. None
 * served state prison time. They don't meet the database's bar
 * for documented political prisoners (>=7 days incarceration).
 *
 * Same pattern as remove_pro_israel_anti_protest_perps.php.
 * Idempotent — bails out per-prisoner if not in DB.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$targets = [
    'aida y. aston',
    'aida yagmur aston',
    'aida y aston',
    'aida aston',
    'alexander jacob castro',
    'alexander castro',
    'kamile dincsoy',
    'alexandria t. fite',
    'alexandria t fite',
    'alexandria fite',
    'elise s. kelder',
    'elise s kelder',
    'elise kelder',
    'oliver e. solares herrera',
    'oliver e solares herrera',
    'oliver solares herrera',
    'oliver solares',
];

$removed = 0; $missing = [];
$seen = [];
foreach ($targets as $name) {
    $p = Prisoner::whereRaw('LOWER(name) = ?', [$name])->first();
    if (! $p) continue;
    if (in_array($p->id, $seen, true)) continue;
    $seen[] = $p->id;

    $cases = $p->cases()->count();
    $p->cases()->delete();
    $p->delete();
    echo "[removed] {$p->name} ({$cases} case rows)\n";
    $removed++;
}

echo "\nDone. removed={$removed}\n";
