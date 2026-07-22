#!/usr/bin/env bash
#
# Declutter the ideology and affiliation taxonomies by removing every value
# that is attached to only ONE prisoner. A tag with a single member is useless
# as a filter/category, so it is stripped from that prisoner's array.
#
# Counts are computed LIVE across the whole prisoner table at run time (not
# hard-coded), so the operation is always correct for the current data. It
# prints the full list of values it removes.
#
# Idempotent: once the singletons are gone, a re-run finds none (removing a
# singleton value does not change any other value's count). Non-destructive to
# multi-member tags. Run from the repo root:
#   bash database/data/remove-singleton-taxonomy.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$fields = ["ideologies" => [], "affiliation" => []];

// Pass 1: count how many prisoners carry each value, per field.
\App\Models\Prisoner::withoutGlobalScopes()->select("id", "ideologies", "affiliation")->chunk(500, function ($chunk) use (&$fields) {
    foreach ($chunk as $p) {
        foreach (array_keys($fields) as $f) {
            foreach ((array) $p->{$f} as $v) {
                $v = trim((string) $v);
                if ($v === "") { continue; }
                $fields[$f][$v] = ($fields[$f][$v] ?? 0) + 1;
            }
        }
    }
});

$singletons = [];
foreach ($fields as $f => $counts) {
    $singletons[$f] = array_flip(array_keys(array_filter($counts, fn ($n) => $n === 1)));
    echo strtoupper($f) . ": " . count($fields[$f]) . " distinct, " . count($singletons[$f]) . " singleton(s) to remove\n";
}

// Pass 2: strip singleton values from each prisoner that carries one.
$changed = 0; $removed = 0;
\App\Models\Prisoner::withoutGlobalScopes()->select("id", "slug", "ideologies", "affiliation")->chunk(500, function ($chunk) use ($singletons, &$changed, &$removed) {
    foreach ($chunk as $p) {
        $dirty = false;
        foreach (["ideologies", "affiliation"] as $f) {
            $vals = (array) $p->{$f};
            if (! $vals) { continue; }
            $kept = array_values(array_filter($vals, function ($v) use ($singletons, $f) {
                return ! isset($singletons[$f][trim((string) $v)]);
            }));
            if (count($kept) !== count($vals)) {
                $removed += count($vals) - count($kept);
                $p->{$f} = $kept ?: null;
                $dirty = true;
            }
        }
        if ($dirty) { $p->save(); $changed++; }
    }
});

echo "\nRemoved values to drop:\n";
foreach ($singletons as $f => $set) {
    foreach (array_keys($set) as $v) { echo "  [{$f}] {$v}\n"; }
}

echo "\nStripped {$removed} singleton value(s) from {$changed} prisoner(s).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Singleton ideologies and affiliations removed."
