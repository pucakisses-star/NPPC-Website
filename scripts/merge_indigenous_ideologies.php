<?php

declare(strict_types=1);

/**
 * Collapse every ideology containing the word "Indigenous"
 * (case-insensitive) into a single canonical "Indigenous Right's
 * Activism" entry per prisoner. Dedupes the resulting array.
 *
 * Examples merged:
 *   - Indigenous sovereignty / Indigenous Sovereignty
 *   - Indigenous Activism / Indigenous activism
 *   - Indigenous-territory defense
 *   - Indigenous-Hispano sovereignty
 *   - Indigenous feminism
 *   - Black Indigenous Sovereignty
 *   - Indigenous
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$canonical = "Indigenous Right's Activism";

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
            if (preg_match('/\bindigenous\b/i', $trimmed)) {
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

echo "\nIndigenous merge complete.\n";
echo sprintf("  Updated:    %d prisoners\n", $updated);
echo sprintf("  Untouched:  %d (no Indigenous variants present)\n", $untouched);
