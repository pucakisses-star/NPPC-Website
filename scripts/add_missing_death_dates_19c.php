<?php

declare(strict_types=1);

/**
 * Backfill death_date on 19th-century prisoners who are showing
 * impossibly high "Age" values (computed from birthdate to today
 * because death_date is null on the record).
 *
 * Death dates verified against standard biographical references
 * (American National Biography, Wikipedia, the Truth Seeker, the
 * Dictionary of American Biography, contemporary obituaries).
 *
 * Idempotent — only writes when death_date is currently null.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

// name (or list of acceptable name spellings) => death_date (Y-m-d)
$dates = [
    ['names' => ['D. M. Bennett', 'DeRobigne Mortimer Bennett', 'D.M. Bennett'],
     'date'  => '1882-12-06'],   // NYC, age 63 (Truth Seeker founder)

    ['names' => ['Ezra Heywood', 'Ezra Hervey Heywood'],
     'date'  => '1893-05-22'],   // Princeton, MA, age 63

    ['names' => ['Abner Kneeland'],
     'date'  => '1844-08-27'],   // Salubria, Iowa, age 70

    ['names' => ['Joseph Palmer'],
     'date'  => '1873-10-30'],   // No Town, MA, age 84

    ['names' => ['Seth Luther'],
     'date'  => '1863-04-29'],   // Vermont Asylum, Brattleboro, age ~68

    ['names' => ['Thomas Wilson Dorr', 'Thomas Dorr'],
     'date'  => '1854-12-27'],   // Providence, RI, age 49
];

$set = 0;
$skip = 0;
$miss = 0;

foreach ($dates as $row) {
    $p = Prisoner::whereIn('name', $row['names'])->first();
    if (! $p) {
        echo "  [not found] " . $row['names'][0] . "\n";
        $miss++;
        continue;
    }
    if ($p->death_date) {
        echo "  [already set] {$p->name}: " . $p->death_date->format('Y-m-d') . "\n";
        $skip++;
        continue;
    }
    $p->death_date = $row['date'];
    $p->save();
    echo "  [set] {$p->name}: death_date = {$row['date']}\n";
    $set++;
}

echo "\nDone. set={$set}, already={$skip}, not found={$miss}\n";
