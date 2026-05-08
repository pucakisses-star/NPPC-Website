<?php

declare(strict_types=1);

/**
 * Remove six hate-crime perpetrators from the prisoner database
 * (these are not political prisoners):
 *
 *   1995 Lancaster CA Nazi Low Riders murder of Milton Walker Jr.:
 *     - Ritch Bryant
 *     - Randall Lee Rojas (aka "Randy")
 *     - Jessica Colwell
 *
 *   2010 Farmington NM Navajo-branding hate crime:
 *     - Paul William Beebe
 *     - William Hatch
 *     - Jesse Alan Sanford
 *
 * Deletes each prisoner's cases first (FK) and then the prisoner row.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$exactNames = [
    'Ritch Bryant',
    'Randy', 'Randall Lee Rojas', 'Randall Rojas', 'Randy (Randall Lee Rojas)',
    'Jessica Colwell',
    'Beebe', 'Paul William Beebe', 'Beebe (Paul William Beebe)',
    'Hatch', 'William Hatch', 'Hatch (William Hatch)',
    'Jesse Alan Sanford', 'Jesse Sanford',
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
