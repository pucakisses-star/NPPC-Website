<?php

declare(strict_types=1);

/**
 * Correct Bill Haywood's in-exile accounting.
 *
 * The PrisonerCase boot hook auto-fills `in_exile_since` to the
 * release_date of every case belonging to a prisoner flagged as
 * `in_exile`/`currently_in_exile`. For Haywood this incorrectly
 * tagged both his 1907 Steunenberg acquittal and his 1919 Leavenworth
 * bond release as the start of exile, producing ~28 years total.
 *
 * He actually fled to Soviet Russia in April 1921 after the Supreme
 * Court denied his appeal and he jumped bail. He died in Moscow on
 * May 18, 1928 — about 7 years in exile.
 *
 * This script:
 *   - Clears in_exile_since / end_of_exile / in_exile_for_days on any
 *     Haywood case that isn't his IWW Espionage Act case.
 *   - On the IWW case, sets in_exile_since = 1921-04-01,
 *     end_of_exile = 1928-05-18, lets the boot hook recompute days.
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$haywood = Prisoner::where('name', 'Bill Haywood')->first();
if (! $haywood) {
    echo "Bill Haywood not found.\n";
    exit(1);
}

// Stop the PrisonerCase boot hook from re-filling in_exile_since on
// every saved case from its release_date.
if ($haywood->in_exile || $haywood->currently_in_exile) {
    $haywood->in_exile = false;
    $haywood->currently_in_exile = false;
    $haywood->save();
    echo "  Cleared prisoner.in_exile / currently_in_exile flags (deceased; exile recorded on the case)\n";
}

$cleared = 0;
$set = 0;
foreach ($haywood->cases as $case) {
    $isIww = stripos((string) $case->charges, 'Espionage Act') !== false
          || stripos((string) $case->charges, 'IWW') !== false
          || ($case->incarceration_date && $case->incarceration_date->year >= 1917 && $case->incarceration_date->year <= 1919);

    if ($isIww) {
        $case->in_exile_since   = '1921-04-01';
        $case->end_of_exile     = '1928-05-18';
        $case->in_exile_for_days = null; // let the boot hook recompute
        $case->save();
        echo "  IWW case: in_exile_since=1921-04-01, end_of_exile=1928-05-18 -> in_exile_for_days={$case->in_exile_for_days}\n";
        $set++;
    } else {
        if ($case->in_exile_since || $case->end_of_exile || $case->in_exile_for_days) {
            $case->in_exile_since    = null;
            $case->end_of_exile      = null;
            $case->in_exile_for_days = null;
            $case->save();
            $charges = $case->charges ? substr($case->charges, 0, 60) . '...' : "(case {$case->id})";
            echo "  Cleared exile fields on non-IWW case: {$charges}\n";
            $cleared++;
        }
    }
}

echo "\nDone. IWW cases set: {$set}, non-IWW cases cleared: {$cleared}.\n";
