<?php

declare(strict_types=1);

/**
 * Fix Cecily McMillan's incarceration window, plus a defensive
 * pass over every case where the recovered HTML import set
 * incarceration_date == arrest_date but the case has a
 * sentenced_date significantly later than arrest_date — meaning
 * the defendant was out on bail until sentencing and shouldn't
 * count the bail period as time served.
 *
 * Cecily's incarceration window: 2014-05-19 (sentenced) -> 2014-07-02
 * (~44 days actual jail; ~58 with weekends she sat that don't count
 * toward the 90-day sentence). The pre-import pre-trial period was
 * spent on bail, not in jail.
 *
 * General fix: any case where
 *   incarceration_date == arrest_date  AND
 *   sentenced_date > arrest_date + 30 days
 * gets incarceration_date promoted to sentenced_date and
 * imprisoned_for_days recomputed from the corrected window.
 *
 * Idempotent. --dry-run shows the plan without writing.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$dryRun = in_array('--dry-run', $argv ?? [], true);

// ---- 1. Cecily McMillan specific fix ------------------------------------
$cecily = Prisoner::whereRaw('LOWER(name) = ?', ['cecily mcmillan'])->first();
if ($cecily) {
    $case = $cecily->cases()->first();
    if ($case) {
        $case->arrest_date        = '2012-03-17';
        $case->sentenced_date     = '2014-05-19';
        $case->incarceration_date = '2014-05-19';
        $case->release_date       = '2014-07-02';
        $case->imprisoned_for_days = 44; // sentenced 2014-05-19 -> released 2014-07-02
        if (! $dryRun) $case->save();
        echo "[fix] Cecily McMillan -> incarceration 2014-05-19, release 2014-07-02, days=44\n";
    }
}

// ---- 2. General sweep: bail-then-sentenced cases ------------------------
$candidates = PrisonerCase::query()
    ->whereNotNull('arrest_date')
    ->whereNotNull('sentenced_date')
    ->whereColumn('arrest_date', '=', 'incarceration_date')
    ->whereRaw("julianday(sentenced_date) - julianday(arrest_date) > 30")
    ->with('prisoner:id,name')
    ->get();

echo "\nGeneral sweep: " . $candidates->count() . " cases where incarceration_date == arrest_date but sentenced > 30 days later.\n";
$swept = 0;
foreach ($candidates as $c) {
    if (! $c->prisoner) continue;
    $oldDays = (int) ($c->imprisoned_for_days ?? 0);
    $newStart = Carbon::parse($c->sentenced_date);
    $newEnd = $c->release_date ? Carbon::parse($c->release_date) : Carbon::now();
    $newDays = max(0, (int) $newStart->diffInDays($newEnd));
    if ($oldDays === $newDays && (string) $c->incarceration_date === (string) $c->sentenced_date) continue;

    echo sprintf("  %s :: %s -> %s, days %d -> %d\n",
        $c->prisoner->name,
        substr((string) $c->incarceration_date, 0, 10),
        $newStart->format('Y-m-d'),
        $oldDays,
        $newDays
    );

    if (! $dryRun) {
        $c->incarceration_date  = $newStart->format('Y-m-d');
        $c->imprisoned_for_days = $newDays;
        $c->save();
    }
    $swept++;
}

echo "\nDone. cecily_fixed=" . ($cecily && $cecily->cases()->exists() ? 'yes' : 'no')
    . ", general_swept={$swept}"
    . ($dryRun ? ' (dry-run)' : '') . "\n";
