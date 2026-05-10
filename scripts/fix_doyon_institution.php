<?php

declare(strict_types=1);

/**
 * Fix Christopher Doyon's case institution. The HTML-recovery
 * import attached his case to "Guantanamo Bay Detention Camp" —
 * data corruption from the source. He was actually held at Santa
 * Rita Jail in Dublin, CA after his June 2021 extradition (the
 * Alameda County jail handles N.D. Cal. federal pretrial overflow
 * on contract). Sentenced to time-served June 28, 2022.
 *
 * Source: Indybay 2021-07-15 prisoner-support post; CBS News;
 * Infosecurity Magazine.
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;
use App\Models\Prisoner;

$p = Prisoner::whereRaw('LOWER(name) = ?', ['christopher doyon'])->first();
if (! $p) {
    echo "Christopher Doyon not found. Aborting.\n";
    exit(1);
}

$case = $p->cases()->orderBy('created_at')->first();
if (! $case) {
    echo "No case row to patch. Aborting.\n";
    exit(1);
}

$santaRita = Institution::firstOrCreate(
    ['name' => 'Santa Rita Jail'],
    [
        'city'             => 'Dublin',
        'state'            => 'California',
        'physical_address' => '5325 Broder Boulevard, Dublin, CA 94568',
    ]
);

$case->institution_id = $santaRita->id;
$case->save();

echo "[fix] Christopher Doyon case institution -> {$santaRita->name} ({$santaRita->city}, {$santaRita->state})\n";
echo "Done.\n";
