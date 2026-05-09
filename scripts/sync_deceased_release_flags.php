<?php

declare(strict_types=1);

/**
 * For every prisoner with a death_date, force:
 *   released            = 1
 *   in_custody          = 0
 *   currently_in_exile  = 0
 *   imprisoned_or_exiled = 0
 *
 * The Prisoner model's saving hook now does this on every save going
 * forward; this is a one-shot reconciliation for rows that
 * pre-date the hook. Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use Illuminate\Support\Facades\DB;

$rows = Prisoner::whereNotNull('death_date')
    ->where(function ($q) {
        $q->where('released', 0)
          ->orWhere('in_custody', 1)
          ->orWhere('currently_in_exile', 1)
          ->orWhere('imprisoned_or_exiled', 1);
    })
    ->get(['id', 'name', 'death_date', 'released', 'in_custody', 'currently_in_exile', 'imprisoned_or_exiled']);

echo "Deceased prisoners with stale active-flags: {$rows->count()}\n";

foreach ($rows as $p) {
    $before = "released={$p->released} in_custody={$p->in_custody} currently_in_exile={$p->currently_in_exile} imprisoned_or_exiled={$p->imprisoned_or_exiled}";
    DB::table('prisoners')->where('id', $p->id)->update([
        'released'             => 1,
        'in_custody'           => 0,
        'currently_in_exile'   => 0,
        'imprisoned_or_exiled' => 0,
    ]);
    echo "  {$p->name} (d. {$p->death_date->format('Y-m-d')}): {$before} -> released=1, in_custody=0, currently_in_exile=0, imprisoned_or_exiled=0\n";
}

echo "\nDone. Updated {$rows->count()} prisoner(s).\n";
