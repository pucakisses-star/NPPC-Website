<?php

declare(strict_types=1);

/**
 * Collapse every "Communist Party USA (...)" affiliation variant
 * (e.g. "Communist Party USA (California chair)",
 *  "Communist Party USA (Illinois/NY chairman)") down to a single
 * "Communist Party USA" entry on each prisoner. Deduplicates the
 * affiliation array. Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$canonical = 'Communist Party USA';
$pattern   = '/^\s*Communist Party USA\b/i';

$touched = 0;
$prisoners = Prisoner::whereNotNull('affiliation')->get(['id', 'name', 'affiliation']);

foreach ($prisoners as $p) {
    $aff = $p->affiliation;
    if (! is_array($aff) || empty($aff)) {
        continue;
    }
    $changed = false;
    $new = [];
    foreach ($aff as $a) {
        $val = is_string($a) ? trim($a) : $a;
        if (is_string($val) && preg_match($pattern, $val) && $val !== $canonical) {
            $val = $canonical;
            $changed = true;
        }
        $new[] = $val;
    }
    // Deduplicate while preserving order
    $deduped = [];
    foreach ($new as $v) {
        if (! in_array($v, $deduped, true)) {
            $deduped[] = $v;
        }
    }
    if ($deduped !== $aff) {
        echo "  {$p->name}: " . json_encode($aff) . " -> " . json_encode($deduped) . "\n";
        $p->affiliation = $deduped;
        $p->save();
        $touched++;
    }
}

echo "\nUpdated {$touched} prisoners.\n";
