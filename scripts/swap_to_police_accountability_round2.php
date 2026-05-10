<?php

declare(strict_types=1);

/**
 * Replace three values with "Police Accountability" everywhere
 * they appear in either ideologies or affiliation:
 *   - Anti-police violence
 *   - Summer 2020 protests
 *   - Anti-police  (and the variants caught by the earlier script)
 *
 * Dedupes after each change. Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$canon  = 'Police Accountability';
$swap   = array_map('mb_strtolower', [
    'Anti-police violence',
    'Summer 2020 protests',
    'Anti-police',
    'Antipolice',
    'Anti-policing',
    'Antipolicing',
]);

$touched = 0;
foreach (Prisoner::query()->cursor() as $p) {
    $changed = false;
    foreach (['ideologies', 'affiliation'] as $field) {
        $arr = $p->$field ?? [];
        if (! is_array($arr) || empty($arr)) continue;

        $out = [];
        foreach ($arr as $v) {
            if (in_array(mb_strtolower(trim((string) $v)), $swap, true)) {
                $out[] = $canon;
                if ($v !== $canon) $changed = true;
            } else {
                $out[] = $v;
            }
        }
        if ($changed) {
            $p->$field = array_values(array_unique($out));
        }
    }
    if ($changed) {
        $p->save();
        echo sprintf("[updated] %s\n", $p->name);
        $touched++;
    }
}

echo "\nDone. Updated {$touched} prisoner(s).\n";
