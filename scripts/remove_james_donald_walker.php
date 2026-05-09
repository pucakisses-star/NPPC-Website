<?php

declare(strict_types=1);

/**
 * Remove the James Donald Walker prisoner entry. He doesn't belong
 * in a political-prisoner database (2018 vehicular-homicide case in
 * Montesano, WA). Deletes his cases first, then the prisoner.
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$p = Prisoner::where('slug', 'james-donald-walker')->first()
    ?? Prisoner::where('name', 'James Donald Walker')->first();

if (! $p) {
    echo "James Donald Walker not found (already removed?).\n";
    exit(0);
}

$caseCount = $p->cases()->count();
$p->cases()->delete();
$p->delete();

echo "Removed James Donald Walker (id={$p->id}, cases deleted={$caseCount}).\n";
