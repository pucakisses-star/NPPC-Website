<?php

declare(strict_types=1);

/**
 * Swap "Puerto Rican self-determination" -> "Puerto Rican Independence"
 * everywhere that label appears in the prisoners.ideologies JSON
 * array. Case-insensitive match; preserves the rest of the array
 * order. Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$from = 'Puerto Rican self-determination';
$to   = 'Puerto Rican Independence';
$key  = mb_strtolower($from);

$touched = 0;
foreach (Prisoner::whereNotNull('ideologies')->get(['id', 'name', 'ideologies']) as $p) {
    $ids = $p->ideologies;
    if (! is_array($ids) || empty($ids)) continue;
    $changed = false;
    $new = [];
    foreach ($ids as $i) {
        if (is_string($i) && mb_strtolower(trim($i)) === $key) {
            $i = $to;
            $changed = true;
        }
        $new[] = $i;
    }
    // Deduplicate (in case the prisoner already has both labels).
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
