<?php

declare(strict_types=1);

/**
 * Re-sync the imprisoned_or_exiled flag on every prisoner so it
 * matches (in_custody OR currently_in_exile). The model's saving
 * hook already does this on every save, but rows that haven't been
 * touched since the hook was added may still be stale. This is a
 * one-shot reconciliation. Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use Illuminate\Support\Facades\DB;

$mismatched = Prisoner::query()
    ->whereRaw('(`in_custody` = 1 OR `currently_in_exile` = 1) <> `imprisoned_or_exiled`')
    ->get(['id', 'name', 'in_custody', 'currently_in_exile', 'imprisoned_or_exiled']);

echo "Stale imprisoned_or_exiled rows: {$mismatched->count()}\n";

foreach ($mismatched as $p) {
    $expected = ($p->in_custody || $p->currently_in_exile) ? 1 : 0;
    DB::table('prisoners')
        ->where('id', $p->id)
        ->update(['imprisoned_or_exiled' => $expected]);
    echo "  {$p->name}: imprisoned_or_exiled " . (int) $p->imprisoned_or_exiled . " -> {$expected}\n";
}

echo "\nDone. Synced {$mismatched->count()} prisoner(s).\n";
