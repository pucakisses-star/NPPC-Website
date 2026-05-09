<?php

declare(strict_types=1);

/**
 * Remove every ideology value that appears on exactly one prisoner.
 * Mirrors scripts/remove_singleton_affiliations.php — see that file
 * for the rationale and pass-by-pass behavior.
 *
 * Idempotent. Supports --dry-run.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$dryRun = in_array('--dry-run', $argv ?? [], true);

$counts = [];

$rows = Prisoner::whereNotNull('ideologies')->get(['id', 'name', 'ideologies']);

foreach ($rows as $p) {
    $ids = $p->ideologies;
    if (! is_array($ids)) continue;
    $seen = [];
    foreach ($ids as $i) {
        if (! is_string($i)) continue;
        $val = trim($i);
        if ($val === '') continue;
        $key = mb_strtolower($val);
        if (isset($seen[$key])) continue;
        $seen[$key] = true;
        $counts[$key] = ($counts[$key] ?? 0) + 1;
    }
}

$singletons = array_keys(array_filter($counts, fn ($n) => $n === 1));
sort($singletons);

echo "Distinct ideologies: " . count($counts) . "\n";
echo "Singletons (used by exactly one prisoner): " . count($singletons) . "\n";

if (empty($singletons)) {
    echo "Nothing to strip.\n";
    return;
}

$touched = 0;
$dropped = 0;

foreach ($rows as $p) {
    $ids = $p->ideologies;
    if (! is_array($ids)) continue;
    $new = [];
    foreach ($ids as $i) {
        if (! is_string($i)) { $new[] = $i; continue; }
        $val = trim($i);
        $key = mb_strtolower($val);
        if (in_array($key, $singletons, true)) {
            $dropped++;
            continue;
        }
        $new[] = $val;
    }
    if ($new !== $ids) {
        echo "  {$p->name}: " . json_encode($ids, JSON_UNESCAPED_UNICODE) . " -> " . json_encode($new, JSON_UNESCAPED_UNICODE) . "\n";
        if (! $dryRun) {
            $p->ideologies = $new ?: null;
            $p->save();
        }
        $touched++;
    }
}

echo "\nDone. prisoners updated={$touched}, ideology values dropped={$dropped}" . ($dryRun ? ' (dry-run)' : '') . "\n";
