<?php

declare(strict_types=1);

/**
 * Set every prisoner with era="Modern" to era="2020s".
 *
 * The earlier normalize_prisoner_eras.php derived eras from case
 * arrest_date, but a small number of prisoners with era="Modern" had
 * no case dates, so they were left unresolved. Per user direction,
 * map them all to "2020s".
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$updated = Prisoner::where('era', 'Modern')->update(['era' => '2020s']);
echo "Updated {$updated} prisoners: era 'Modern' -> '2020s'\n";
