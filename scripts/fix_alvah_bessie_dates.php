<?php

declare(strict_types=1);

/**
 * Fix Alvah Bessie's Hollywood Ten case data:
 *   - Backfill incarceration_date = 1950-06-09 on his case
 *   - If multiple Hollywood Ten cases exist for him (post-merge),
 *     consolidate to a single case with full date data and delete
 *     the rest.
 *
 * Confirmed facts: Bessie surrendered to FCI Texarkana on June 9,
 * 1950 after SCOTUS denied cert; served 10 months; released
 * April 9, 1951. (Some sources give release as June 15, 1951 — that
 * appears to be the formal sentence end including good-time
 * adjustments.)
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$bessie = Prisoner::where('name', 'Alvah Cecil Bessie')
    ->orWhere('name', 'Alvah Bessie')
    ->first();

if (! $bessie) {
    echo "Alvah Bessie: NOT FOUND\n";
    exit(1);
}

echo "Found {$bessie->name} (id={$bessie->id})\n";
echo "Existing cases: " . $bessie->cases()->count() . "\n";

// Find his Hollywood Ten case(s)
$cases = $bessie->cases()->orderBy('created_at')->get();

if ($cases->isEmpty()) {
    echo "No cases found\n";
    exit(1);
}

// Pick the case with the most data (richest record) as the canonical
$canonical = $cases->sortByDesc(function ($c) {
    return strlen((string) $c->charges) + strlen((string) $c->sentence)
         + ($c->incarceration_date ? 100 : 0)
         + ($c->arrest_date ? 50 : 0)
         + ($c->release_date ? 50 : 0)
         + ($c->sentenced_date ? 50 : 0)
         + ($c->judge ? 25 : 0);
})->first();

// Apply known-good Hollywood Ten date data
if (empty($canonical->arrest_date)) $canonical->arrest_date = '1947-10-28';
if (empty($canonical->sentenced_date)) $canonical->sentenced_date = '1950-06-09';
if (empty($canonical->incarceration_date)) $canonical->incarceration_date = '1950-06-09';
if (empty($canonical->release_date)) $canonical->release_date = '1951-04-09';
if (empty($canonical->judge)) $canonical->judge = 'Hon. Edward M. Curran (D.D.C.)';
$canonical->save();
$canonical->refresh();

echo "Canonical case: id={$canonical->id}\n";
echo "  arrest_date         = {$canonical->arrest_date}\n";
echo "  sentenced_date      = {$canonical->sentenced_date}\n";
echo "  incarceration_date  = {$canonical->incarceration_date}\n";
echo "  release_date        = {$canonical->release_date}\n";
echo "  imprisoned_for_days = " . var_export($canonical->imprisoned_for_days, true) . "\n";

// Delete any other duplicate Hollywood Ten cases (post-merge cleanup)
foreach ($cases as $c) {
    if ($c->id === $canonical->id) continue;
    // Only delete if it looks like the same Hollywood Ten case
    $charges = strtolower((string) $c->charges);
    if (str_contains($charges, 'hollywood ten') || str_contains($charges, 'huac') || str_contains($charges, 'contempt of congress')) {
        echo "Deleting duplicate Hollywood Ten case id={$c->id}\n";
        $c->delete();
    }
}

echo "Done.\n";
