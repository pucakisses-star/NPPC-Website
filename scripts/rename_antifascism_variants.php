<?php

declare(strict_types=1);

/**
 * Normalize all antifascism-related ideology variants to a single
 * canonical value: "Antifascism".
 *
 * Variants merged:
 *   - "Anti-fascism"
 *   - "Anti-fascist"
 *   - "Anti-Fascism"
 *   - "Antifascist"
 *   - "Antifascism"  (already canonical; deduped if mixed with variants)
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$variants = [
    'Anti-fascism', 'Anti-fascist', 'Anti-Fascism',
    'Antifascist', 'Antifascism',
];
$canonical = 'Antifascism';
$variantMap = array_flip($variants);

$updated = 0;
$untouched = 0;

Prisoner::whereNotNull('ideologies')->chunkById(200, function ($chunk) use (
    &$updated, &$untouched, $variantMap, $canonical
) {
    foreach ($chunk as $prisoner) {
        $current = $prisoner->ideologies;
        if (! is_array($current) || empty($current)) { $untouched++; continue; }

        $hasVariant = false;
        $rebuilt = [];
        foreach ($current as $value) {
            if (is_string($value) && isset($variantMap[$value])) {
                $hasVariant = true;
                $rebuilt[] = $canonical;
            } else {
                $rebuilt[] = $value;
            }
        }

        if (! $hasVariant) { $untouched++; continue; }

        $deduped = array_values(array_unique($rebuilt));
        $prisoner->ideologies = $deduped;
        $prisoner->save();
        $updated++;
        echo sprintf("  [%s] -> %s\n", $prisoner->name, implode(', ', $deduped));
    }
});

echo "\nAntifascism variant rename complete.\n";
echo sprintf("  Updated:    %d prisoners\n", $updated);
echo sprintf("  Untouched:  %d (no antifascism variants present)\n", $untouched);
echo "\n";
