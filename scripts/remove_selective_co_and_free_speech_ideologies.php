<?php

declare(strict_types=1);

/**
 * Remove "Selective conscientious objection" and "Free Speech" from
 * every prisoner's ideologies array. Case-insensitive match on
 * either label so trivial spelling/punctuation variants are caught.
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$targets = [
    'selective conscientious objection',
    'free speech',
];

$touched = 0;

foreach (Prisoner::whereNotNull('ideologies')->get(['id', 'name', 'ideologies']) as $p) {
    $ids = $p->ideologies;
    if (! is_array($ids) || empty($ids)) continue;

    $new = array_values(array_filter($ids, function ($i) use ($targets) {
        if (! is_string($i)) return true;
        $norm = strtolower(trim($i));
        return ! in_array($norm, $targets, true);
    }));

    if ($new !== $ids) {
        echo "  {$p->name}: " . json_encode($ids) . " -> " . json_encode($new) . "\n";
        $p->ideologies = $new;
        $p->save();
        $touched++;
    }
}

echo "\nRemoved target ideologies from {$touched} prisoner(s).\n";
