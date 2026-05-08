<?php

declare(strict_types=1);

/**
 * Two ideology cleanups:
 *   1. Remove "Western Federation of Miners" from every prisoner's
 *      ideologies array (it's an organization, not an ideology).
 *   2. Rename "Socialist" -> "Socialism" wherever it appears in
 *      ideologies arrays. Dedupes if both already present.
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$updated = 0;
$untouched = 0;

Prisoner::whereNotNull('ideologies')->chunkById(200, function ($chunk) use (&$updated, &$untouched) {
    foreach ($chunk as $prisoner) {
        $current = $prisoner->ideologies;
        if (! is_array($current) || empty($current)) { $untouched++; continue; }

        $changed = false;
        $rebuilt = [];
        foreach ($current as $value) {
            if (! is_string($value)) { $rebuilt[] = $value; continue; }
            if ($value === 'Western Federation of Miners') {
                $changed = true;
                continue; // drop it
            }
            if ($value === 'Socialist') {
                $rebuilt[] = 'Socialism';
                $changed = true;
                continue;
            }
            $rebuilt[] = $value;
        }

        if (! $changed) { $untouched++; continue; }

        $deduped = array_values(array_unique($rebuilt));
        $prisoner->ideologies = $deduped ?: null;
        $prisoner->save();
        $updated++;
        echo sprintf("  [%s] -> %s\n", $prisoner->name, $deduped ? implode(', ', $deduped) : '(cleared)');
    }
});

echo "\nIdeology label cleanup complete.\n";
echo sprintf("  Updated:    %d prisoners\n", $updated);
echo sprintf("  Untouched:  %d (no matching values)\n", $untouched);
