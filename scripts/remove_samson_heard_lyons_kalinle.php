<?php

declare(strict_types=1);

/**
 * Remove four hate-crime perpetrators (not political prisoners):
 *   - Emanuel Kidega Samson  (Antioch TN church shooting, 2017)
 *   - Thomas / "Tasha" Heard (NYC pepper-spray attacks, 2019)
 *   - Todd Lyons             (NYC hate-crime assaults, 2019)
 *   - Nimo Jire Kalinle      (Portland bus assault, 2020)
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$names = [
    'Emanuel Kidega Samson',
    'Tasha Heard',
    'Thomas Heard',
    'Tasha Heard (Thomas Heard)',
    'Todd Lyons',
    'Nimo Jire Kalinle',
];

$prisoners = Prisoner::whereIn('name', $names)->get();

$nP = 0; $nC = 0;
foreach ($prisoners as $p) {
    $caseCount = $p->cases()->count();
    $p->cases()->delete();
    $p->delete();
    $nP++;
    $nC += $caseCount;
    echo "  Removed: {$p->name} (id={$p->id}, cases={$caseCount})\n";
}

echo "\nDone. Prisoners deleted: {$nP}, cases deleted: {$nC}\n";
