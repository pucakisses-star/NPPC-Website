<?php

declare(strict_types=1);

/**
 * Clean up the `state` field on prisoners:
 *   - Null out any state whose value contains "in exile" or "deported"
 *     (case-insensitive). These are statuses, not U.S. states.
 *   - Null out any state equal to "United States" (the country, not a
 *     state category).
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$exileLike = Prisoner::whereNotNull('state')
    ->where(function ($q) {
        $q->where('state', 'like', '%in exile%')
          ->orWhere('state', 'like', '%deported%');
    })
    ->get(['id', 'name', 'state']);

foreach ($exileLike as $p) {
    echo "  clearing state for {$p->name}: '{$p->state}'\n";
}
$nExile = Prisoner::whereNotNull('state')
    ->where(function ($q) {
        $q->where('state', 'like', '%in exile%')
          ->orWhere('state', 'like', '%deported%');
    })
    ->update(['state' => null]);

$us = Prisoner::whereIn('state', ['United States', 'united states', 'USA', 'U.S.', 'U.S.A.'])
    ->get(['id', 'name', 'state']);
foreach ($us as $p) {
    echo "  clearing state for {$p->name}: '{$p->state}'\n";
}
$nUs = Prisoner::whereIn('state', ['United States', 'united states', 'USA', 'U.S.', 'U.S.A.'])
    ->update(['state' => null]);

echo "\nCleared exile/deported state on {$nExile} prisoners.\n";
echo "Cleared 'United States' state on {$nUs} prisoners.\n";
