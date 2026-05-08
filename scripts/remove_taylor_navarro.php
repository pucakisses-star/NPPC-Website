<?php

declare(strict_types=1);

/**
 * Remove two hate-crime perpetrators from the prisoners DB:
 *   - Ronald Taylor (March 1, 2000 Wilkinsburg PA shooting rampage)
 *   - Daniel D. Navarro (July 3, 2020 Wisconsin motorcycle homicide)
 *
 * Deletes each prisoner's cases first, then the prisoner row.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$exactNames = [
    'Ronald Taylor',
    'Daniel D. Navarro',
    'Daniel Navarro',
];

$prisoners = Prisoner::whereIn('name', $exactNames)->get();

$deletedPrisoners = 0;
$deletedCases = 0;

foreach ($prisoners as $p) {
    $caseCount = $p->cases()->count();
    $p->cases()->delete();
    $p->delete();
    $deletedPrisoners++;
    $deletedCases += $caseCount;
    echo "  Removed: {$p->name} (id={$p->id}, cases={$caseCount})\n";
}

echo "\nDone.\n";
echo sprintf("  Prisoners deleted: %d\n", $deletedPrisoners);
echo sprintf("  Cases deleted:     %d\n", $deletedCases);
