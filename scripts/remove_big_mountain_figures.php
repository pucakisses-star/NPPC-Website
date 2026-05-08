<?php

declare(strict_types=1);

/**
 * Remove the ten Big Mountain Diné resistance figures from the
 * prisoners DB. Per user audit, none served meaningful jail time:
 *   - Four were arrested at the July 11, 2001 Camp Anna Mae
 *     Sundance raid and released ROR after one night in Hopi jail
 *     (Whitesinger, Ruth Benally, Louise Benally, Ashkie).
 *   - Katherine Smith got a directed verdict of acquittal in her
 *     1979 firearms case; pretrial status undocumented.
 *   - Mae Tso reportedly briefly detained / cited in 1986.
 *   - Roberta Blackgoat, Glenna Begay, Bahe Keediniihii, and
 *     John Benally were never arrested.
 *
 * Their organizing is significant but does not fit a political-
 * prisoner database scope.
 *
 * Deletes each prisoner's cases first, then the prisoner row.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$exactNames = [
    'Roberta Blackgoat',
    'Pauline Whitesinger',
    'Katherine Smith',
    'Mae Tso',
    'Mae Wilson Tso',
    'Mae Tso (Mae Wilson Tso)',
    'Glenna Begay',
    'Bahe Keediniihii',
    'Bahe Y. Katenay',
    'Bahe Keediniihii (Bahe Y. Katenay)',
    'John Benally',
    'Ruth Benally',
    'Louise Benally',
    'Joella Ashkie',
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
