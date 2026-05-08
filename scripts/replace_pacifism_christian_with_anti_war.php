<?php

declare(strict_types=1);

/**
 * Replace ideology values "Pacifism" and "Christian peace activism"
 * (case-insensitive, after trim) with "Anti-War". Dedupes the
 * resulting array.
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$canonical = 'Anti-War';
$matches = [
    'pacifism',
    'christian peace activism',
];

$updated = 0;
$untouched = 0;

Prisoner::whereNotNull('ideologies')->chunkById(200, function ($chunk) use (&$updated, &$untouched, $canonical, $matches) {
    foreach ($chunk as $prisoner) {
        $current = $prisoner->ideologies;
        if (! is_array($current) || empty($current)) { $untouched++; continue; }

        $changed = false;
        $rebuilt = [];
        foreach ($current as $value) {
            if (! is_string($value)) { $rebuilt[] = $value; continue; }
            $trimmed = trim($value);
            if (in_array(mb_strtolower($trimmed), $matches, true)) {
                if ($trimmed !== $canonical) $changed = true;
                $rebuilt[] = $canonical;
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

echo "\nReplace Pacifism / Christian peace activism -> Anti-War complete.\n";
echo sprintf("  Updated:    %d prisoners\n", $updated);
echo sprintf("  Untouched:  %d (no matching values)\n", $untouched);
