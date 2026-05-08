<?php

declare(strict_types=1);

/**
 * Remove two prisoners from the database (not political prisoners):
 *   - Michael Steven Sandford
 *   - Naveed Afzal Haq
 *
 * Also remove two ideology values from every prisoner's array:
 *   - "Attorney-client privilege"
 *   - "Civil rights law"
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$names = [
    'Michael Steven Sandford', 'Michael Sandford',
    'Naveed Afzal Haq', 'Naveed Haq',
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

echo "\nPrisoners deleted: {$nP}, cases deleted: {$nC}\n";

// ---- Ideology cleanup ----
$denyMap = array_flip(['Attorney-client privilege', 'Civil rights law']);
$ideologyUpdated = 0;

Prisoner::whereNotNull('ideologies')->chunkById(200, function ($chunk) use (&$ideologyUpdated, $denyMap) {
    foreach ($chunk as $p) {
        $current = $p->ideologies;
        if (! is_array($current) || empty($current)) continue;
        $filtered = [];
        foreach ($current as $v) {
            if (! is_string($v)) { $filtered[] = $v; continue; }
            if (! isset($denyMap[trim($v)])) $filtered[] = $v;
        }
        if (count($filtered) === count($current)) continue;
        $p->ideologies = $filtered ?: null;
        $p->save();
        $ideologyUpdated++;
    }
});

echo "Ideologies cleaned on {$ideologyUpdated} prisoners.\n";
