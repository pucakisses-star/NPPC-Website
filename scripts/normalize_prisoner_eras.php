<?php

declare(strict_types=1);

/**
 * Normalize prisoner.era values to decade format.
 *
 * Allowed values:
 *   1700s, 1800s, 1900s, 1910s, 1920s, 1930s, 1940s, 1950s,
 *   1960s, 1970s, 1980s, 1990s, 2000s, 2010s, 2020s
 *
 * Rule:
 *   - year < 1800             -> "1700s"
 *   - 1800 <= year < 1900     -> "1800s"
 *   - year >= 1900            -> "<decade>s" e.g. 1965 -> "1960s"
 *
 * Year sourced from (in order of preference):
 *   1. Earliest case arrest_date
 *   2. Earliest case incarceration_date
 *   3. Earliest case sentenced_date
 *   4. Prisoner birthdate + 25 years (rough adult-activity proxy)
 *
 * Idempotent: prisoners whose era already matches the allowed set are
 * skipped.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$allowed = [
    '1700s', '1800s',
    '1900s', '1910s', '1920s', '1930s', '1940s', '1950s',
    '1960s', '1970s', '1980s', '1990s', '2000s', '2010s', '2020s',
];

$decadeFromYear = static function (int $year): string {
    if ($year < 1800) return '1700s';
    if ($year < 1900) return '1800s';
    return ((int) floor($year / 10) * 10) . 's';
};

$yearOf = static function (?string $date): ?int {
    if (! $date) return null;
    $y = (int) substr($date, 0, 4);
    return $y > 0 ? $y : null;
};

$updated = 0;
$skipped = 0;
$unresolved = [];

Prisoner::with('cases')->whereNotIn('era', $allowed)->orWhereNull('era')->chunk(200, function ($chunk) use (
    &$updated, &$skipped, &$unresolved, $allowed, $decadeFromYear, $yearOf
) {
    foreach ($chunk as $prisoner) {
        if (in_array($prisoner->era, $allowed, true)) { $skipped++; continue; }

        $year = null;
        $earliest = $prisoner->cases->sortBy(function ($c) use ($yearOf) {
            return $yearOf($c->arrest_date) ?? $yearOf($c->incarceration_date) ?? $yearOf($c->sentenced_date) ?? PHP_INT_MAX;
        })->first();

        if ($earliest) {
            $year = $yearOf($earliest->arrest_date)
                 ?? $yearOf($earliest->incarceration_date)
                 ?? $yearOf($earliest->sentenced_date);
        }
        if ($year === null && $prisoner->birthdate) {
            $b = $yearOf($prisoner->birthdate);
            if ($b) $year = $b + 25;
        }

        if ($year === null) {
            $unresolved[] = sprintf('%s (id=%s, era=%s)', $prisoner->name, $prisoner->id, $prisoner->era);
            continue;
        }

        $new = $decadeFromYear($year);
        $old = $prisoner->era;
        $prisoner->era = $new;
        $prisoner->save();
        $updated++;
        echo sprintf("  [%-22s] -> [%s]  %s (year=%d)\n", $old ?? 'NULL', $new, $prisoner->name, $year);
    }
});

echo "\nEra normalization complete.\n";
echo sprintf("  Updated:    %d prisoners\n", $updated);
echo sprintf("  Skipped:    %d (already in allowed set)\n", $skipped);
echo sprintf("  Unresolved: %d (no case dates and no birthdate)\n", count($unresolved));
foreach ($unresolved as $line) echo "    - $line\n";
echo "\n";
