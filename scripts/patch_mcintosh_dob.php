<?php

declare(strict_types=1);

/**
 * Set Christopher W. McIntosh's birthdate to 1982-01-01 — year
 * verified from cross-referencing the December 16, 2005 DOJ
 * sentencing press release ("23 years old", from Maple Shade NJ)
 * with the BOP inmate locator's current-age record. Exact month
 * and day are not in the publicly accessible record. The 01-01
 * placeholder makes him sort and filter correctly by birth year
 * without claiming a precise date we don't actually have.
 *
 * Idempotent — only writes if birthdate is blank or differs.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$p = Prisoner::whereRaw('LOWER(name) IN (?, ?, ?)', [
    'christopher w. mcintosh',
    'christopher w mcintosh',
    'christopher mcintosh',
])->first();
if (! $p) {
    echo "Christopher W. McIntosh not found.\n";
    exit(1);
}

if ((string) $p->birthdate === '1982-01-01' || (string) $p->birthdate === '1982-01-01 00:00:00') {
    echo "[noop] {$p->name} already has birthdate 1982-01-01.\n";
    exit(0);
}

$p->birthdate = '1982-01-01';
$p->save();

echo "[fix] {$p->name} -> birthdate 1982-01-01 (year verified, month/day approximate)\n";
echo "Done.\n";
