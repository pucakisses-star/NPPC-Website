<?php

declare(strict_types=1);

/**
 * Remove the Nancy Sweeney prisoner entry. Out of scope for a
 * political-prisoner database (2020 Niles IL hate-crime/battery
 * case against an 87-year-old neighbor). Deletes attached cases
 * first, then the prisoner. Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$p = Prisoner::where('slug', 'nancy-sweeney')->first()
    ?? Prisoner::where('name', 'Nancy Sweeney')->first();

if (! $p) {
    echo "Nancy Sweeney not found (already removed?).\n";
    exit(0);
}

$caseCount = $p->cases()->count();
$p->cases()->delete();
$p->delete();

echo "Removed Nancy Sweeney (id={$p->id}, cases deleted={$caseCount}).\n";
