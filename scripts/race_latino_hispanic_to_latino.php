<?php

declare(strict_types=1);

/**
 * Replace race "Latino/Hispanic" with "Latino" everywhere.
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$updated = Prisoner::where('race', 'Latino/Hispanic')->update(['race' => 'Latino']);
echo "Updated {$updated} prisoners: race 'Latino/Hispanic' -> 'Latino'\n";
