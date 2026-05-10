<?php

declare(strict_types=1);

/**
 * Backfill arrest_date = 2002-05-22 for Jim Cosner, Nassim
 * Zerriffi, and Vincent Lombardo. All three were arrested at
 * the May 22, 2002 anti-Iraq-war demonstration in Santa Cruz, CA
 * where police sparked a melee while protesters confronted U.S.
 * Rep. Sam Farr over his pro-war stance. Source: Nuclear Resister
 * Issue #118 (early 2003); same data the original
 * add_santa_cruz_may2002_protest.php script tried to record.
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use App\Models\PrisonerCase;

$names = ['Jim Cosner', 'Nassim Zerriffi', 'Vincent Lombardo'];
$arrest = '2002-05-22';

foreach ($names as $name) {
    $p = Prisoner::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
    if (! $p) {
        echo "[missing] {$name}\n";
        continue;
    }

    $case = $p->cases()->orderBy('created_at')->first();
    if (! $case) {
        // No case row at all — create one with the documented info
        $case = $p->cases()->create([
            'charges'     => 'California Penal Code § 148(a)(1) — resisting arrest. Arrested at a Santa Cruz, CA anti-Iraq-war demonstration on May 22, 2002.',
            'arrest_date' => $arrest,
        ]);
        echo "[create] {$name} -> new case row (id={$case->id}) with arrest_date={$arrest}\n";
        continue;
    }

    if ((string) $case->arrest_date === $arrest . ' 00:00:00' || (string) $case->arrest_date === $arrest) {
        echo "[noop]   {$name} already has arrest_date={$arrest}\n";
        continue;
    }

    $case->arrest_date = $arrest;
    $case->save();
    echo "[set]    {$name} -> arrest_date={$arrest}\n";
}

echo "\nDone.\n";
