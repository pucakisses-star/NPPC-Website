<?php

declare(strict_types=1);

/**
 * Remove "Columbia University" from every prisoner's affiliation
 * array. Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$target = 'Columbia University';
$touched = 0;

foreach (Prisoner::whereNotNull('affiliation')->get(['id', 'name', 'affiliation']) as $p) {
    $aff = $p->affiliation;
    if (! is_array($aff) || empty($aff)) continue;

    $new = array_values(array_filter($aff, fn ($a) => ! (is_string($a) && trim($a) === $target)));
    if ($new !== $aff) {
        echo "  {$p->name}: " . json_encode($aff) . " -> " . json_encode($new) . "\n";
        $p->affiliation = $new;
        $p->save();
        $touched++;
    }
}

echo "\nRemoved '{$target}' from {$touched} prisoner(s).\n";
