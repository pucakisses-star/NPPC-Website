<?php

declare(strict_types=1);

/**
 * Split the multi-facility institution row
 *   "USP Lewisburg / FCI Danbury / FCI Petersburg"
 *   (id e023697d-2179-421e-8396-9f06fbfbdca9)
 * into three real institutions. For every prisoner_case currently
 * pointing at the multi-facility row, clone the case into THREE
 * cases — one per facility — preserving every other field
 * (charges, dates, sentence, judge, etc.). Then delete the
 * multi-facility row.
 *
 * Effect: prisoners who served time at all three facilities now
 * have one case per facility, which is what the source HTML
 * implied. May inflate per-prisoner case counts on the dashboard
 * (the prosecution itself was one event); adjust if needed.
 *
 * Idempotent: bails out cleanly if the multi-facility row is
 * already gone.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\DB;

$dryRun = in_array('--dry-run', $argv ?? [], true);

$multiId = 'e023697d-2179-421e-8396-9f06fbfbdca9';

// Canonical IDs the existing institutions table already has:
$targets = [
    'df689c4b-2900-4bb4-bb53-55fd5fd1abd7', // United States Penitentiary, Lewisburg
    'cbe5f67e-97ff-40a8-96f7-1be4adb0b6e5', // FCI Danbury
    '43e21e09-4d40-49d9-9585-4c3c22642f01', // FCI Petersburg
];

$multi = Institution::find($multiId);
if (! $multi) {
    echo "[skip] multi-facility row {$multiId} already gone — nothing to do.\n";
    return;
}

// Sanity check the targets exist
foreach ($targets as $tid) {
    if (! Institution::find($tid)) {
        echo "ERROR: target institution {$tid} not found. Aborting.\n";
        exit(1);
    }
}

$cases = PrisonerCase::where('institution_id', $multiId)->get();
echo "Multi-facility row: {$multi->name}\n";
echo "Cases currently attached: {$cases->count()}\n";
echo "Will clone each case into " . count($targets) . " new case rows (one per facility).\n";
echo "After: original case row stays linked to first target; two new cases get inserted per original.\n\n";

if ($cases->isEmpty()) {
    echo "Nothing to clone. Just deleting the multi-facility row.\n";
    if (! $dryRun) $multi->delete();
    echo "Done.\n";
    return;
}

foreach ($cases as $c) {
    echo sprintf("  case %s (prisoner_id=%s)\n",
        substr($c->id, 0, 8),
        substr($c->prisoner_id, 0, 8)
    );
    foreach ($targets as $i => $tid) {
        $instName = Institution::find($tid)->name;
        if ($i === 0) {
            echo sprintf("    [reassign existing -> %s] %s\n", substr($tid, 0, 8), $instName);
        } else {
            echo sprintf("    [clone   new       -> %s] %s\n", substr($tid, 0, 8), $instName);
        }
    }
}

if ($dryRun) {
    echo "\nDry run — nothing written. Re-run without --dry-run to execute.\n";
    return;
}

DB::transaction(function () use ($cases, $targets, $multi) {
    foreach ($cases as $c) {
        // 1. Reassign the existing row to the first target
        $c->institution_id = $targets[0];
        $c->save();

        // 2. Clone for the other targets (skip target[0] since it's
        //    already linked above)
        for ($i = 1; $i < count($targets); $i++) {
            $clone = $c->replicate();
            $clone->institution_id = $targets[$i];
            $clone->save();
        }
    }

    // 3. Delete the multi-facility row
    $multi->delete();
});

echo "\nDone.\n";
