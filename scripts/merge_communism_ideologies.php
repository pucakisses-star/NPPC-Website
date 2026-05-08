<?php

declare(strict_types=1);

/**
 * Collapse all "Communism..." and "Communist" ideology variants into
 * a single canonical "Communism" entry per prisoner.
 *
 * Matched (replaced with "Communism"):
 *   - Anything starting with "Communism" (case-insensitive)
 *     e.g. "Communism", "Communism (former)", "Communism (CPUSA)"
 *   - Exact "Communist" (case-insensitive after trim)
 *
 * Deliberately NOT matched (preserved unchanged):
 *   - "Anti-communism", "Anti-Communist"
 *   - "Marxism-Leninism" (does not contain Communism/Communist)
 *
 * Dedupes the resulting array. Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$canonical = 'Communism';

$updated = 0;
$untouched = 0;

Prisoner::whereNotNull('ideologies')->chunkById(200, function ($chunk) use (&$updated, &$untouched, $canonical) {
    foreach ($chunk as $prisoner) {
        $current = $prisoner->ideologies;
        if (! is_array($current) || empty($current)) { $untouched++; continue; }

        $changed = false;
        $rebuilt = [];
        foreach ($current as $value) {
            if (! is_string($value)) { $rebuilt[] = $value; continue; }
            $trimmed = trim($value);

            $matches = false;
            if (stripos($trimmed, 'Communism') === 0) {
                $matches = true;
            } elseif (strcasecmp($trimmed, 'Communist') === 0) {
                $matches = true;
            }

            if ($matches) {
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

echo "\nCommunism merge complete.\n";
echo sprintf("  Updated:    %d prisoners\n", $updated);
echo sprintf("  Untouched:  %d (no Communism/Communist variants present)\n", $untouched);
