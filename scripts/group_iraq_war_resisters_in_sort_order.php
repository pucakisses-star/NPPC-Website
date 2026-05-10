<?php

declare(strict_types=1);

/**
 * Group Iraq War resisters together in the prisoners sort_order.
 *
 * Identifies candidates by description text patterns (Iraq + a
 * resistance keyword), sorts them by arrest date (chronological
 * within the group), then renumbers sort_order for the affected
 * range so the whole group occupies consecutive sort_order values
 * starting at the position the lowest-sorted group member already
 * had.
 *
 * Strategy: rebuild the global prisoner list in sorted order,
 * pull the group out, reinsert it as a contiguous block at the
 * anchor position, then renumber sort_order 1...N for everyone.
 * This preserves the relative ordering of non-group prisoners.
 *
 * --dry-run shows the plan without writing.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use Illuminate\Support\Facades\DB;

$dryRun = in_array('--dry-run', $argv ?? [], true);

// ---- 1. Find Iraq War resisters --------------------------------
//
// Patterns deliberately conservative: must mention Iraq plus an
// explicit resistance keyword. Won't catch generic "Anti-war"
// without Iraq specificity.
$candidates = Prisoner::query()
    ->where(function ($q) {
        $patterns = [
            '%Iraq War%',
            '%Iraq war%',
            '%refused to deploy to Iraq%',
            '%refused deployment to Iraq%',
            '%refused orders to Iraq%',
            '%deserted%Iraq%',
            '%conscientious objector%Iraq%',
            '%Iraq Veterans Against the War%',
            '%anti-Iraq-war%',
            '%anti-Iraq war%',
        ];
        foreach ($patterns as $p) {
            $q->orWhere('description', 'LIKE', $p);
        }
    })
    ->orWhereHas('cases', function ($q) {
        $q->where('charges', 'LIKE', '%refused%Iraq%')
          ->orWhere('charges', 'LIKE', '%Iraq%refus%')
          ->orWhere('charges', 'LIKE', '%desertion%Iraq%');
    })
    ->orderBy('arrest_date_min') // not a real column; will be ignored if missing
    ->get();

// Filter out plainly-irrelevant matches (Iraq-context-only mentions)
$resisters = $candidates->filter(function ($p) {
    $bio = (string) $p->description;
    // Must still mention Iraq AND a resistance/refusal keyword
    if (! preg_match('/iraq/i', $bio)) return false;
    return (bool) preg_match('/refus|desert|object|resist|AWOL|conscientious|Iraq Veterans Against the War/i', $bio);
});

$resisters = $resisters->sortBy(function ($p) {
    // chronological by earliest case arrest_date if available, else
    // by name as fallback
    $earliest = optional($p->cases->first())->arrest_date;
    return $earliest ? (string) $earliest : 'zzz_' . $p->name;
})->values();

if ($resisters->isEmpty()) {
    echo "No Iraq War resisters matched. Aborting.\n";
    exit(0);
}

echo "==== Iraq War resister group (" . $resisters->count() . " prisoners, in chronological order) ====\n";
foreach ($resisters as $p) {
    $earliest = optional($p->cases->first())->arrest_date;
    echo sprintf("  sort=%-5s  arrest=%-12s  %s\n",
        $p->sort_order,
        $earliest ? substr((string) $earliest, 0, 10) : '-',
        $p->name
    );
}

// ---- 2. Compute new sort_order assignment ----------------------
$resisterIds = $resisters->pluck('id')->all();
$anchorSortOrder = (int) $resisters->min('sort_order');

$all = Prisoner::query()->orderBy('sort_order')->get(['id', 'name', 'sort_order']);

// Split: prisoners with sort_order < anchor stay first; group goes
// next; remaining non-group prisoners follow in their existing
// relative order.
$beforeAnchor = $all->filter(fn ($p) => $p->sort_order < $anchorSortOrder && ! in_array($p->id, $resisterIds, true))->values();
$afterAnchor  = $all->filter(fn ($p) => $p->sort_order >= $anchorSortOrder && ! in_array($p->id, $resisterIds, true))->values();

$newOrder = $beforeAnchor->concat($resisters)->concat($afterAnchor)->values();

echo "\n==== Sort_order changes (only renumbering when changed) ====\n";
$changes = 0;
$updates = []; // [id => newSortOrder]
foreach ($newOrder as $idx => $p) {
    $newSortOrder = $idx + 1;
    if ($p->sort_order !== $newSortOrder) {
        $updates[$p->id] = $newSortOrder;
        $changes++;
    }
}
echo sprintf("  %d prisoners would have their sort_order changed.\n", $changes);

if ($dryRun) {
    echo "\nDry run — nothing written. Re-run without --dry-run to execute.\n";
    return;
}

// ---- 3. Write -----------------------------------------------------
DB::transaction(function () use ($updates) {
    foreach ($updates as $id => $sortOrder) {
        DB::table('prisoners')->where('id', $id)->update(['sort_order' => $sortOrder]);
    }
});

echo "Done. Wrote {$changes} sort_order updates.\n";
echo "Iraq War resister group anchored at sort_order " . $newOrder->search(fn ($p) => in_array($p->id, $resisterIds ?? [], true)) . "+1.\n";
