<?php

declare(strict_types=1);

/**
 * Rename ideology "Abolitionist" -> "Abolitionism" everywhere.
 * Dedupes if both already present.
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
            $trimmed = trim($value);
            if (strcasecmp($trimmed, 'Abolitionist') === 0) {
                $rebuilt[] = 'Abolitionism';
                $changed = true;
            } else {
                $rebuilt[] = $trimmed;
            }
        }

        $deduped = array_values(array_unique($rebuilt));
        if (count($deduped) !== count($current)) $changed = true;

        if (! $changed) { $untouched++; continue; }

        $prisoner->ideologies = $deduped ?: null;
        $prisoner->save();
        $updated++;
        echo sprintf("  [%s] -> %s\n", $prisoner->name, implode(', ', $deduped));
    }
});

echo "\nAbolitionist -> Abolitionism rename complete.\n";
echo sprintf("  Updated:    %d prisoners\n", $updated);
echo sprintf("  Untouched:  %d (no Abolitionist value present)\n", $untouched);
