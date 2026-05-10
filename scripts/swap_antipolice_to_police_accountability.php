<?php

declare(strict_types=1);

/**
 * Replace "Anti-police" (and case/spelling variants) in every
 * prisoner's ideologies array with "Police Accountability".
 * Dedupes if both were already present.
 *
 * Idempotent — re-running after a clean pass is a no-op.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$variants = ['anti-police', 'antipolice', 'anti police', 'anti-policing', 'antipolicing'];
$canon    = 'Police Accountability';

$touched = 0;
foreach (Prisoner::query()->whereNotNull('ideologies')->cursor() as $p) {
    $ideologies = $p->ideologies ?? [];
    if (! is_array($ideologies) || empty($ideologies)) continue;

    $changed = false;
    $out = [];
    foreach ($ideologies as $i) {
        if (in_array(mb_strtolower(trim((string) $i)), $variants, true)) {
            $out[] = $canon;
            $changed = true;
        } else {
            $out[] = $i;
        }
    }
    if (! $changed) continue;

    $deduped = array_values(array_unique($out));
    $p->ideologies = $deduped;
    $p->save();
    echo "  [updated] {$p->name}  ->  " . implode(', ', $deduped) . "\n";
    $touched++;
}

echo "\nDone. Updated {$touched} prisoner(s).\n";
