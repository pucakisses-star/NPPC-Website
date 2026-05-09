<?php

declare(strict_types=1);

/**
 * Clear the aka field on every prisoner whose aka contains a
 * semicolon. Most of these are auto-generated long-form name
 * permutations (e.g. "Sherman Austin; Sherman Martin Austin")
 * rather than meaningful aliases. Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$rows = Prisoner::whereNotNull('aka')
    ->where('aka', 'like', '%;%')
    ->get(['id', 'name', 'aka']);

$cleared = 0;
foreach ($rows as $p) {
    echo "  {$p->name}: cleared aka '{$p->aka}'\n";
    $p->aka = null;
    $p->save();
    $cleared++;
}

echo "\nDone. Cleared aka on {$cleared} prisoner(s).\n";
