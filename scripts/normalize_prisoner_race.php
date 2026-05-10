<?php

declare(strict_types=1);

/**
 * Race normalization:
 *   - "Unknown"  -> null  (clear it)
 *   - "Biracial" -> null  (clear it)
 *   - "Arab"     -> "Middle Eastern"
 *   - "American Indian" -> "Native American"
 *   - "Native Hawaiian" / "Native Hawaiian/Pacific Islander"
 *     / "Pacific Islander" -> "Native American"
 *
 * Case-insensitive matching. Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$clear = ['unknown', 'biracial'];

// raw lower-cased -> canonical replacement
$swap = [
    'arab'                              => 'Middle Eastern',
    'american indian'                   => 'Native American',
    'native hawaiian'                   => 'Native American',
    'native hawaiian/pacific islander'  => 'Native American',
    'native hawaiian / pacific islander'=> 'Native American',
    'pacific islander'                  => 'Native American',
];

$cleared = 0; $swapped = 0;
foreach (Prisoner::query()->whereNotNull('race')->cursor() as $p) {
    $raw = trim((string) $p->race);
    $lc  = mb_strtolower($raw);

    if (in_array($lc, $clear, true)) {
        $p->race = null;
        $p->save();
        echo "[cleared] {$p->name}  ({$raw} -> null)\n";
        $cleared++;
        continue;
    }

    if (isset($swap[$lc])) {
        $new = $swap[$lc];
        if ($raw !== $new) {
            $p->race = $new;
            $p->save();
            echo "[swap]    {$p->name}  ({$raw} -> {$new})\n";
            $swapped++;
        }
    }
}

echo "\nDone. cleared={$cleared}, swapped={$swapped}\n";
