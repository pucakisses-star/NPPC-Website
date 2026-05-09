<?php

declare(strict_types=1);

/**
 * Remove "NVE" and "Accelerationism" from every prisoner's
 * ideologies array. Case-insensitive. Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$targets = ['nve', 'accelerationism'];
$touched = 0;

foreach (Prisoner::whereNotNull('ideologies')->get(['id', 'name', 'ideologies']) as $p) {
    $ids = $p->ideologies;
    if (! is_array($ids) || empty($ids)) continue;

    $new = array_values(array_filter($ids, function ($i) use ($targets) {
        if (! is_string($i)) return true;
        return ! in_array(strtolower(trim($i)), $targets, true);
    }));

    if ($new !== $ids) {
        echo "  {$p->name}: " . json_encode($ids) . " -> " . json_encode($new) . "\n";
        $p->ideologies = $new;
        $p->save();
        $touched++;
    }
}

echo "\nRemoved target ideologies from {$touched} prisoner(s).\n";
