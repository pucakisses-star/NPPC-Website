<?php

declare(strict_types=1);

/**
 * Four cleanups in one script:
 *
 *   1. Remove ideology "Cultural criticism" from every prisoner.
 *   2. Remove every ideology that starts with "Catholic" (case-
 *      insensitive) EXCEPT the exact value "Catholic Worker".
 *      (Drops "Catholic radicalism", "Catholic peace tradition",
 *      "Catholic pacifism", "Catholic peace witness", "Catholic
 *      social teaching", "Catholic Left", "Catholic", etc.)
 *   3. Rename ideology "Communism (former)" -> "Communism" (with
 *      dedupe if both already present).
 *   4. Delete the prisoner record (and cases) for Eric Leonardo
 *      Charron.
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

// ---- 1, 2, 3: ideology array cleanups ----
$updated = 0;
$untouched = 0;

Prisoner::whereNotNull('ideologies')->chunkById(200, function ($chunk) use (&$updated, &$untouched) {
    foreach ($chunk as $prisoner) {
        $current = $prisoner->ideologies;
        if (! is_array($current) || empty($current)) { $untouched++; continue; }

        $changed = false;
        $rebuilt = [];
        foreach ($current as $value) {
            if (! is_string($value)) { $rebuilt[] = $value; continue; }
            $trimmed = trim($value);

            if ($trimmed === 'Cultural criticism') {
                $changed = true;
                continue;
            }

            // Drop anything Catholic-prefixed except exact "Catholic Worker"
            if (stripos($trimmed, 'Catholic') === 0 && $trimmed !== 'Catholic Worker') {
                $changed = true;
                continue;
            }

            // Rename Communism (former) -> Communism
            if ($trimmed === 'Communism (former)') {
                $rebuilt[] = 'Communism';
                $changed = true;
                continue;
            }

            $rebuilt[] = $trimmed;
        }

        if (! $changed) { $untouched++; continue; }

        $deduped = array_values(array_unique($rebuilt));
        $prisoner->ideologies = $deduped ?: null;
        $prisoner->save();
        $updated++;
        echo sprintf("  [%s] -> %s\n", $prisoner->name, $deduped ? implode(', ', $deduped) : '(cleared)');
    }
});

echo "\nIdeology cleanups complete.\n";
echo sprintf("  Updated:    %d prisoners\n", $updated);
echo sprintf("  Untouched:  %d (no matching values)\n", $untouched);

// ---- 4: delete Eric Leonardo Charron ----
$charron = Prisoner::whereIn('name', [
    'Eric Leonardo Charron',
    'Eric Charron',
])->get();

$deletedPrisoners = 0;
$deletedCases = 0;
foreach ($charron as $p) {
    $caseCount = $p->cases()->count();
    $p->cases()->delete();
    $p->delete();
    $deletedPrisoners++;
    $deletedCases += $caseCount;
    echo "  Removed: {$p->name} (id={$p->id}, cases={$caseCount})\n";
}

echo "\nCharron removal complete.\n";
echo sprintf("  Prisoners deleted: %d\n", $deletedPrisoners);
echo sprintf("  Cases deleted:     %d\n", $deletedCases);
