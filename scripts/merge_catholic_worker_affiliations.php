<?php

declare(strict_types=1);

/**
 * Merge every affiliation containing "Catholic Worker" (case-
 * insensitive) into a single canonical "Catholic Worker" entry on
 * each prisoner. Dedupes the resulting array.
 *
 * Examples collapsed to "Catholic Worker":
 *   - "Catholic Worker Movement"
 *   - "Catholic Worker"
 *   - "New York Catholic Worker"
 *   - "Catholic Worker Hospitality House"
 *   - "Catholic Peace Fellowship / Catholic Worker"
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$canonical = 'Catholic Worker';

$updated = 0;
$untouched = 0;

Prisoner::whereNotNull('affiliation')->chunkById(200, function ($chunk) use (&$updated, &$untouched, $canonical) {
    foreach ($chunk as $prisoner) {
        $current = $prisoner->affiliation;
        if (! is_array($current) || empty($current)) { $untouched++; continue; }

        $changed = false;
        $rebuilt = [];
        foreach ($current as $value) {
            if (! is_string($value)) { $rebuilt[] = $value; continue; }
            $trimmed = trim($value);
            if (stripos($trimmed, 'Catholic Worker') !== false) {
                if ($trimmed !== $canonical) $changed = true;
                $rebuilt[] = $canonical;
            } else {
                $rebuilt[] = $trimmed;
            }
        }

        $deduped = array_values(array_unique($rebuilt));
        if (count($deduped) !== count($current)) $changed = true;

        if (! $changed) { $untouched++; continue; }

        $prisoner->affiliation = $deduped ?: null;
        $prisoner->save();
        $updated++;
        echo sprintf("  [%s] -> %s\n", $prisoner->name, implode(', ', $deduped));
    }
});

echo "\nCatholic Worker merge complete.\n";
echo sprintf("  Updated:    %d prisoners\n", $updated);
echo sprintf("  Untouched:  %d (no Catholic Worker variants present)\n", $untouched);
