<?php

declare(strict_types=1);

/**
 * Reclassify the Minneapolis 18 SWP / Teamsters Local 544 leadership
 * — defendants in the first Smith Act trial (United States v. Dunne
 * et al., 1941; convicted 1941; appeals exhausted Dec 1943; served
 * Dec 1943 – Feb 1945) — from era "1950s" to era "1940s".
 *
 * Their action was 1941, prosecution 1941, conviction 1941, federal
 * incarceration window 1943-1945. The 1950s tag in the DB doesn't
 * fit any phase of the case. Idempotent — only writes when era is
 * currently "1950s" or unset.
 *
 * Names mirror the Minneapolis 18 cohort. Some weren't found in the
 * DB on the prior audit; if they're added later they'll get the
 * same treatment by the era condition.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$cohort = [
    'James P. Cannon',
    'Vincent Raymond Dunne',
    'Carl Skoglund',
    'Felix Morrow',
    'Albert Goldman',
    'Farrell Dobbs',
    'Grace Holmes Carlson',
    'Max Geldman',
    'Harry DeBoer',
    'Edward Palmquist',
    'Carlos Hudson',
    'Jake Cooper',
    'Karl Kuehn',
    'Roy Orgon',
    'Oscar Schoenfeld',
    'Clarence Hamel',
    'Alfred Russell',
    'Emil Hansen',
];

$updated = 0;
$noChange = 0;
$missing = 0;

foreach ($cohort as $name) {
    $p = Prisoner::where('name', $name)->first();
    if (! $p) {
        echo "  [warn]   {$name} not in DB — skipping\n";
        $missing++;
        continue;
    }
    $current = (string) $p->era;
    if ($current === '1940s') {
        echo "  [ok]     {$p->name} — already 1940s\n";
        $noChange++;
        continue;
    }
    $p->era = '1940s';
    $p->save();
    echo "  [update] {$p->name}: era '{$current}' -> '1940s'\n";
    $updated++;
}

echo "\nDone. updated={$updated}, no_change={$noChange}, missing={$missing}\n";
