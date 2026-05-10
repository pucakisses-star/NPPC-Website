<?php

declare(strict_types=1);

/**
 * Round 2 ideology cleanup — strip three more values from every
 * prisoner's ideologies array. Case-insensitive. Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$kill = array_map('mb_strtolower', [
    'Anti-Israel',
    'First Amendment',
    'Anti-Jewish',
]);

$touched = 0;
foreach (Prisoner::query()->whereNotNull('ideologies')->cursor() as $p) {
    $arr = $p->ideologies ?? [];
    if (! is_array($arr) || empty($arr)) continue;

    $out = [];
    $changed = false;
    foreach ($arr as $v) {
        if (in_array(mb_strtolower(trim((string) $v)), $kill, true)) {
            $changed = true;
            continue;
        }
        $out[] = $v;
    }
    if (! $changed) continue;

    $p->ideologies = array_values(array_unique($out));
    $p->save();
    echo sprintf("[updated] %s -> [%s]\n", $p->name, implode(', ', $p->ideologies));
    $touched++;
}

echo "\nDone. Updated {$touched} prisoner(s).\n";
