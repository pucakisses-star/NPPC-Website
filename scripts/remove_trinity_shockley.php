<?php

declare(strict_types=1);

/**
 * Remove the Trinity Shockley (AKA Jamie Shockley, Dex Shockley)
 * prisoner entry. Out of scope — 2025 Indiana school-shooting
 * conspiracy case, not a political prisoner. Deletes attached cases
 * first, then the prisoner. Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$p = Prisoner::where('slug', 'trinity-shockley')->first()
    ?? Prisoner::whereIn('name', ['Trinity Shockley', 'Jamie Shockley', 'Dex Shockley'])->first();

if (! $p) {
    echo "Trinity/Jamie/Dex Shockley not found (already removed?).\n";
    exit(0);
}

$caseCount = $p->cases()->count();
$p->cases()->delete();
$p->delete();

echo "Removed {$p->name} (id={$p->id}, cases deleted={$caseCount}).\n";
