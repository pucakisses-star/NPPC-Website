<?php

declare(strict_types=1);

/**
 * Remove the 11 Asheville Aston Park (December 2021) homeless-rights
 * rally defendants from the prisoners DB.
 *
 * Most pleaded out to community service or had charges voluntarily
 * dismissed; not appropriate for a political-prisoner database.
 *
 * Deletes each prisoner's cases first, then the prisoner row.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$exactNames = [
    'Elsa Ann Larson Enstrom',
    'Pip Flickinger',
    'Elizabeth Ann Flickinger',
    'Pip Flickinger (Elizabeth Ann Flickinger)',
    'Julia Rose Weber',
    'Kathryn Anne Hudson',
    'Pageant Quinn Nevel',
    'Amy Elizabeth Hamilton-Thibert',
    'Alexander Jory Bergdahl',
    'Gina Marie Dickhaus',
    'Sarah Jackson Boddy Norris',
    'Erica Jan Deaton',
    'Nicole A. Matute Villagran',
    'Nicole A Matute Villagran',
    'Nicole Matute Villagran',
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
