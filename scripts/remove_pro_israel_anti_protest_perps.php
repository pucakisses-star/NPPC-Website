<?php

declare(strict_types=1);

/**
 * Remove five prisoners that don't fit the political-prisoner
 * frame — they were Pro-Israel anti-protest perpetrators (keying
 * cars / counter-attacking encampments), and got probation /
 * therapy diversion / charges dismissed.
 *
 *   - Bryan Long
 *   - Lisa Melanie Karlovsky
 *   - Matthew Karlovsky
 *   - UCLAMaroonHoodie (Eyal Shalom)
 *   - UCLANeffHat (Malachi Joshua Marlan-Librett)
 *
 * Matches by lowercased exact name. For each, deletes any cases
 * (cascade FK would do this anyway) and then the prisoner row.
 * Idempotent — bails out per-prisoner if not in DB.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$targets = [
    'bryan long',
    'lisa melanie karlovsky',
    'matthew karlovsky',
    'uclamaroonhoodie (eyal shalom)',
    'uclaneffhat (malachi joshua marlan-librett)',
];

$removed = 0; $missing = 0;
foreach ($targets as $name) {
    $p = Prisoner::whereRaw('LOWER(name) = ?', [$name])->first();
    if (! $p) {
        echo "[missing] {$name}\n";
        $missing++;
        continue;
    }
    $cases = $p->cases()->count();
    $p->cases()->delete();
    $p->delete();
    echo "[removed] {$p->name} ({$cases} case rows)\n";
    $removed++;
}

echo "\nDone. removed={$removed}, missing={$missing}\n";
