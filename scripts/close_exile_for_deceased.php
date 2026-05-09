<?php

declare(strict_types=1);

/**
 * Close out exile-time accounting for any deceased prisoner.
 *
 * The PrisonerCase boot hook calculates `in_exile_for_days` as
 * (today - in_exile_since) whenever `end_of_exile` is null. For
 * prisoners who died in exile (e.g. Bill Haywood, d. 1928 in Moscow)
 * this otherwise reports 100+ years.
 *
 * For every case where `in_exile_since` is set, `end_of_exile` is
 * null, and the prisoner has a `death_date`, set
 * `end_of_exile = death_date`. Re-saving each case lets the boot
 * hook recompute `in_exile_for_days` from the closed range.
 *
 * Also clears the prisoner's `currently_in_exile` flag for any
 * deceased prisoner (they are not currently in exile — they died).
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$cases = 0;
$deceasedPrisoners = Prisoner::whereNotNull('death_date')->get();

foreach ($deceasedPrisoners as $p) {
    foreach ($p->cases as $case) {
        if ($case->in_exile_since && ! $case->end_of_exile) {
            $case->end_of_exile = $p->death_date;
            $case->save(); // boot hook recomputes in_exile_for_days
            $cases++;
            echo "  {$p->name}: end_of_exile = {$p->death_date->format('Y-m-d')} (in_exile_for_days now {$case->in_exile_for_days})\n";
        }
    }
    if ($p->currently_in_exile) {
        $p->currently_in_exile = false;
        $p->save();
        echo "  {$p->name}: cleared currently_in_exile (deceased)\n";
    }
}

echo "\nClosed exile accounting on {$cases} case(s) for deceased prisoners.\n";
