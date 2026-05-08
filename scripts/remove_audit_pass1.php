<?php

declare(strict_types=1);

/**
 * Audit Pass 1 removals — 15 prisoners flagged by agent review of
 * 224 candidates with no jail dates. All confirmed in agent classification
 * as never having served meaningful jail time.
 *
 * (Empty/no-data entries deliberately retained per user direction.)
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$names = [
    // Charges dropped / informant / never charged
    'Ali Rehman',
    'Ahmadullah Sais Niazi',
    'Robert Hardy',
    'Sgt. Frank "Greg" Ford', 'Sgt. Frank Ford', 'Frank Ford',
    'Ray Boudreaux',
    'Richard Brown',
    "Richard O'Neal",
    'Thomas Adams',
    'Benjamin Moye', 'Benjamin Mayer',
    // Pure organizers / cite-and-release
    'Anne Symens-Bucher',
    'Bill Wylie-Kellermann',
    'Jack Cohen-Joppa',
    'Jim Wallis',
    'Carolyn Rodriguez',
    'Omali Yeshitela',
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
