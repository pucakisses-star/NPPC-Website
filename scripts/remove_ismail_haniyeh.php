<?php

declare(strict_types=1);

/**
 * Remove Ismail Haniyeh from the database.
 *
 * Pattern matches scripts/remove_james_donald_walker.php etc. —
 * delete the prisoner's cases first, then the prisoner row.
 * The cascade FK on prisoner_cases would handle cases on its own,
 * but doing it explicitly makes the intent obvious in logs.
 *
 * Idempotent: if the prisoner doesn't exist, prints a notice and
 * exits cleanly.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$p = Prisoner::where('slug', 'ismail-haniyeh')
    ->orWhereRaw('LOWER(name) LIKE ?', ['%ismail haniyeh%'])
    ->first();

if (! $p) {
    echo "Ismail Haniyeh not found — nothing to remove.\n";
    return;
}

$caseCount = $p->cases()->count();
echo "Found: {$p->name} (id={$p->id}, slug={$p->slug}, cases={$caseCount})\n";

$p->cases()->delete();
$p->delete();

echo "Deleted prisoner and {$caseCount} case row(s).\n";
