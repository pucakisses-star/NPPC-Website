<?php

declare(strict_types=1);

/**
 * Deduplicate every prisoner's ideologies array using a normalized
 * comparison key (trim, NFKC normalize if intl available, casefold,
 * collapse internal whitespace). Preserves the first canonical
 * spelling encountered.
 *
 * Catches cases like two "Antifascism" pills that survived the
 * earlier exact-match dedupe because of leading/trailing whitespace
 * or invisible character differences.
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$normalize = static function (string $s): string {
    if (class_exists(\Normalizer::class)) {
        $n = \Normalizer::normalize($s, \Normalizer::FORM_KC);
        if (is_string($n)) $s = $n;
    }
    $s = preg_replace('/\s+/u', ' ', $s);
    return mb_strtolower(trim($s));
};

$updated = 0;
$untouched = 0;

Prisoner::whereNotNull('ideologies')->chunkById(200, function ($chunk) use (&$updated, &$untouched, $normalize) {
    foreach ($chunk as $prisoner) {
        $current = $prisoner->ideologies;
        if (! is_array($current) || count($current) <= 1) { $untouched++; continue; }

        $seen = [];
        $rebuilt = [];
        foreach ($current as $value) {
            if (! is_string($value)) { $rebuilt[] = $value; continue; }
            $trimmed = trim($value);
            $key = $normalize($trimmed);
            if ($key === '' || isset($seen[$key])) continue;
            $seen[$key] = true;
            $rebuilt[] = $trimmed; // preserve original casing of first-seen
        }

        if (count($rebuilt) === count($current)) { $untouched++; continue; }

        $prisoner->ideologies = $rebuilt ?: null;
        $prisoner->save();
        $updated++;
        echo sprintf("  [%s] %d -> %d  (%s)\n",
            $prisoner->name, count($current), count($rebuilt), implode(', ', $rebuilt));
    }
});

echo "\nIdeology dedupe complete.\n";
echo sprintf("  Updated:    %d prisoners\n", $updated);
echo sprintf("  Untouched:  %d (no duplicates)\n", $untouched);
