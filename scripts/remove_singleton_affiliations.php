<?php

declare(strict_types=1);

/**
 * Remove every affiliation value that appears on exactly one prisoner.
 *
 * Procedure:
 *   1. Walk all prisoners and tally how many distinct prisoners use
 *      each affiliation string (case-insensitive trim, but the
 *      original casing is preserved when comparing values to remove).
 *   2. For every affiliation whose count is 1, find the prisoner who
 *      holds it and strip it from their affiliation array.
 *
 * Idempotent: a second run finds zero singletons because every
 * remaining value either appears on >=2 prisoners or has been removed.
 *
 * Use --dry-run to preview without writing.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$dryRun = in_array('--dry-run', $argv ?? [], true);

// Pass 1: count per (case-insensitive trim) key, but remember the
// original-casing prisoner-side value(s) so we can strip them later.
$counts        = [];   // key => int
$originalsBy   = [];   // key => set of original strings actually seen on prisoners

$rows = Prisoner::whereNotNull('affiliation')->get(['id', 'name', 'affiliation']);

foreach ($rows as $p) {
    $aff = $p->affiliation;
    if (! is_array($aff)) continue;
    $seenInThisRow = [];
    foreach ($aff as $a) {
        if (! is_string($a)) continue;
        $val = trim($a);
        if ($val === '') continue;
        $key = mb_strtolower($val);
        if (isset($seenInThisRow[$key])) continue; // don't double-count if the same prisoner has a duplicate entry
        $seenInThisRow[$key] = true;
        $counts[$key]                   = ($counts[$key]                  ?? 0) + 1;
        $originalsBy[$key][$val]        = true;
    }
}

$singletons = array_keys(array_filter($counts, fn ($n) => $n === 1));
sort($singletons);

echo "Distinct affiliations: " . count($counts) . "\n";
echo "Singletons (used by exactly one prisoner): " . count($singletons) . "\n";

if (empty($singletons)) {
    echo "Nothing to strip.\n";
    return;
}

// Pass 2: for each singleton key, strip every original-casing variant
// from the one prisoner that holds it.
$touched = 0;
$dropped = 0;

foreach ($rows as $p) {
    $aff = $p->affiliation;
    if (! is_array($aff)) continue;
    $new = [];
    foreach ($aff as $a) {
        if (! is_string($a)) { $new[] = $a; continue; }
        $val = trim($a);
        $key = mb_strtolower($val);
        if (in_array($key, $singletons, true)) {
            // drop this value
            $dropped++;
            continue;
        }
        $new[] = $val;
    }
    if ($new !== $aff) {
        echo "  {$p->name}: " . json_encode($aff, JSON_UNESCAPED_UNICODE) . " -> " . json_encode($new, JSON_UNESCAPED_UNICODE) . "\n";
        if (! $dryRun) {
            $p->affiliation = $new ?: null;
            $p->save();
        }
        $touched++;
    }
}

echo "\nDone. prisoners updated={$touched}, affiliation values dropped={$dropped}" . ($dryRun ? ' (dry-run)' : '') . "\n";
