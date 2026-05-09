<?php

declare(strict_types=1);

/**
 * Normalize the era field on prisoners that use named eras instead of
 * the decade-format used everywhere else. Per the user, "Civil War"
 * becomes "1800s". Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$mappings = [
    'Civil War' => '1800s',
];

$total = 0;
foreach ($mappings as $from => $to) {
    $prisoners = Prisoner::where('era', $from)->get(['id', 'name']);
    foreach ($prisoners as $p) {
        echo "  [update] {$p->name}: era '{$from}' -> '{$to}'\n";
    }
    $n = Prisoner::where('era', $from)->update(['era' => $to]);
    $total += $n;
}

echo "\nDone. Era values normalized on {$total} prisoner(s).\n";
