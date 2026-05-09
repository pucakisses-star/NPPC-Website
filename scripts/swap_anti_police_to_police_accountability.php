<?php

declare(strict_types=1);

/**
 * Swap any "Anti-police*" ideology label to "Police Accountability"
 * across every prisoner's ideologies JSON array. Matches:
 *   - "Anti-police"
 *   - "Anti-police violence"
 *   - any other ideology starting with "anti-police" (case-insensitive)
 *
 * Deduplicates the array if both the old and new label coexist on
 * the same prisoner. Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$replacement = 'Police Accountability';
$touched     = 0;

foreach (Prisoner::whereNotNull('ideologies')->get(['id', 'name', 'ideologies']) as $p) {
    $ids = $p->ideologies;
    if (! is_array($ids) || empty($ids)) continue;

    $new = [];
    $changed = false;
    foreach ($ids as $i) {
        if (is_string($i) && preg_match('/^\s*anti-police\b/i', $i)) {
            $i = $replacement;
            $changed = true;
        }
        $new[] = $i;
    }

    // Deduplicate while preserving order.
    $deduped = [];
    foreach ($new as $v) {
        if (! in_array($v, $deduped, true)) $deduped[] = $v;
    }

    if ($changed || $deduped !== $ids) {
        echo "  {$p->name}: " . json_encode($ids, JSON_UNESCAPED_UNICODE) . " -> " . json_encode($deduped, JSON_UNESCAPED_UNICODE) . "\n";
        $p->ideologies = $deduped;
        $p->save();
        $touched++;
    }
}

echo "\nDone. Updated {$touched} prisoner(s).\n";
