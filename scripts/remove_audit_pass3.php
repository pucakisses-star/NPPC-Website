<?php

declare(strict_types=1);

/**
 * Audit Pass 3 removals — 22 prisoners flagged by agent review of 26
 * candidates with Convicted=Yes but probation/suspended/community-
 * service-only sentences and no documented pretrial detention.
 *
 * AMBIGUOUS retained: Barbara Katt (Plowshares Silo Pruning Hooks),
 * Theodora Pollok (Sacramento 46 IWW). Sheila Parks and Suzanne
 * Schmidt KEPT (served one year of three-year sentence).
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$names = [
    'Galen Sol Shireman-Grabowski',
    'Brian Okum',
    'Paul Douglas Revak',
    'Robert Czernik',
    'Garrett Fitzgerald',
    'Nathanael Secor',
    'Max Specktor',
    'Israel L. Hernandez',
    'George A. Vassilatos',
    'Jamil Salem Sarsour',
    'Coleman Boyd',
    'Eva Edl',
    'Eva Zastrow',
    'James Zastrow',
    'Paul Place',
    'Terrence Clyne',
    'Chase A. Iron Eyes',
    'Anthony Clark',
    'Ramón Dagoberto Quiñones',
    'Philip M. Willis-Conger',
    'Margaret Jean Hutchison',
    'Wendy LeWin',
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
