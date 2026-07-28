<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Place every prisoner still sitting at sort_order 0.
 *
 * The list runs newest-first: sort_order 1 is the most recent case and the
 * oldest sit at the bottom. Verified against the live data — the median case
 * year is 2025 around sort 1-50, 1968 around 3000, and 1777 at the very
 * bottom.
 *
 * prisoners:auto-place-zero-sort slots a record beside the already-positioned
 * cluster sharing its affiliation, which keeps groups together but only helps
 * a record that HAS an affiliation matching a placed one; everything else it
 * reports and leaves at zero. prisoners:place-zero-sort handles the remainder
 * from a hand-written table of slug-to-anchor pairs, which does not generalise
 * to whatever gets added next.
 *
 * This is the general fallback: affiliation cluster first, so groups stay
 * contiguous, then the chronology. Nothing is left behind for want of a rule.
 *
 * COHORTS ARE KEYED ON THE PRIMARY YEAR — the earliest date across a
 * prisoner's cases — computed identically for the record being placed and for
 * every record already on the list. Matching on "any case in year Y" instead
 * puts the anchor in the wrong place whenever someone has cases decades apart:
 * Eugene Debs has cases in 1894 and 1918 and sits at his 1894 position, so an
 * any-year match would drop a 1918 record next to the 1890s.
 *
 * Dry by default. --apply writes.
 */
final class PlaceZeroSortByYear extends Command
{
    protected $signature = 'prisoners:place-zero-sort-by-year
        {--apply : Write the new positions (default is a dry run)}';

    protected $description = 'Place every sort_order=0 prisoner, by affiliation cluster where one exists and by case year otherwise';

    /** @var array<string, int|null> prisoner id => primary year */
    private array $years = [];

    /** @var array<int, array{min: int, max: int}> primary year => sort_order span */
    private array $cohorts = [];

    /** @var array<int, string> sort_order => label, for reporting neighbours */
    private array $positioned = [];

    /** Earliest year this prisoner is anchored to, or null when nothing dates them. */
    private function yearOf(Prisoner $p): ?int
    {
        if (array_key_exists($p->id, $this->years)) {
            return $this->years[$p->id];
        }

        $year = null;

        foreach ($p->cases as $case) {
            foreach (['incarceration_date', 'arrest_date', 'sentenced_date', 'in_exile_since', 'release_date'] as $field) {
                if (! $case->{$field}) {
                    continue;
                }
                $candidate = (int) Carbon::parse($case->{$field})->year;
                if ($candidate > 1000) {
                    $year = $year ? min($year, $candidate) : $candidate;
                }
            }
        }

        // An era like "1910s" is worth a decade-accurate guess when no case is dated.
        if (! $year && $p->era && preg_match('/\d{4}/', (string) $p->era, $m)) {
            $year = (int) $m[0];
        }

        return $this->years[$p->id] = $year;
    }

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $all = Prisoner::withoutGlobalScopes()->with('cases')->get();
        $unplaced = $all->where('sort_order', 0)->values();

        if ($unplaced->isEmpty()) {
            $this->info('Nothing at sort_order 0 — the list is fully placed.');

            return self::SUCCESS;
        }

        $this->buildCohorts($all);

        $this->line($unplaced->count().' record(s) at sort_order 0.');
        $this->line('List runs newest-first; cohorts are keyed on each prisoner\'s earliest case year.');
        $this->newLine();

        // Newest first, matching the list, so each insert lands above the ones
        // handled after it and the shifting stays predictable.
        $unplaced = $unplaced->sort(function (Prisoner $a, Prisoner $b) {
            return [$this->yearOf($b) ?? 0, $a->name] <=> [$this->yearOf($a) ?? 0, $b->name];
        })->values();

        $placed = 0;
        $stuck = [];

        foreach ($unplaced as $p) {
            $year = $this->yearOf($p);
            [$newSort, $why] = $this->targetFor($p, $year);

            if ($newSort === null) {
                $stuck[] = $p;
                continue;
            }

            [$above, $below] = $this->neighboursOf($newSort);

            $this->line(sprintf(
                '  %s  %-30s -> sort %-6d %s',
                $year ? (string) $year : '????',
                mb_strimwidth($p->name, 0, 30),
                $newSort,
                $why,
            ));
            $this->line("        above: {$above}");
            $this->line("        below: {$below}");

            if ($apply) {
                Prisoner::withoutGlobalScopes()
                    ->where('id', '!=', $p->id)
                    ->where('sort_order', '>=', $newSort)
                    ->increment('sort_order');
                $p->sort_order = $newSort;
                $p->save();
            }

            // Keep the in-memory picture in step, so a dry run reads like a real
            // one and successive placements do not all target the same slot.
            $this->reserve($newSort, $p, $year);
            $placed++;
        }

        if ($stuck) {
            $this->newLine();
            $this->warn(count($stuck).' record(s) could not be placed — no affiliation cluster and nothing to date them:');
            foreach ($stuck as $s) {
                $this->line('  '.str_pad($s->slug, 34).' era: '.($s->era ?: '-'));
            }
            $this->line('Give them an era or a case date and re-run.');
        }

        if ($apply) {
            Cache::forget(PrisonerApiController::cacheKey());
        }

        $this->newLine();
        if ($apply) {
            $this->info("Placed {$placed} record(s).");
            $this->verify();
        } else {
            $this->warn("Dry run — nothing written. {$placed} record(s) would be placed.");
            $this->line('Re-run with --apply to write.');
        }

        return self::SUCCESS;
    }

    /** Primary-year cohorts and the sort_order labels, from one pass over the table. */
    private function buildCohorts($all): void
    {
        foreach ($all as $p) {
            if ($p->sort_order == 0) {
                continue;
            }

            $sort = (int) $p->sort_order;
            $year = $this->yearOf($p);
            $this->positioned[$sort] = $p->name.' ('.($year ?? '????').')';

            if ($year === null) {
                continue;
            }

            $this->cohorts[$year] = [
                'min' => min($this->cohorts[$year]['min'] ?? PHP_INT_MAX, $sort),
                'max' => max($this->cohorts[$year]['max'] ?? -1, $sort),
            ];
        }
    }

    /**
     * Where this record belongs, and why, or [null, ...] if nothing anchors it.
     *
     * @return array{0: int|null, 1: string}
     */
    private function targetFor(Prisoner $p, ?int $year): array
    {
        // 1. beside its own group, so cases tried together stay together
        foreach (is_array($p->affiliation) ? $p->affiliation : [] as $aff) {
            $clusterMax = Prisoner::withoutGlobalScopes()
                ->where('sort_order', '!=', 0)
                ->where('affiliation', 'like', '%'.$aff.'%')
                ->max('sort_order');

            if ($clusterMax) {
                return [(int) $clusterMax + 1, 'end of the '.$aff.' cluster'];
            }
        }

        if (! $year) {
            return [null, 'undatable'];
        }

        // 2. with its own cohort, at the end of it
        if (isset($this->cohorts[$year])) {
            return [$this->cohorts[$year]['max'] + 1, 'end of the '.$year.' cohort'];
        }

        // 3. immediately above the next-older cohort
        for ($y = $year - 1; $y >= $year - 400; $y--) {
            if (isset($this->cohorts[$y])) {
                return [$this->cohorts[$y]['min'], 'just above the '.$y.' cohort'];
            }
        }

        // 4. older than anything on the list
        return [max(array_keys($this->positioned)) + 1, 'oldest on the list'];
    }

    /** Record a placement in the in-memory picture, shifting everything below it down. */
    private function reserve(int $sort, Prisoner $p, ?int $year): void
    {
        $shifted = [];
        foreach ($this->positioned as $s => $label) {
            $shifted[$s >= $sort ? $s + 1 : $s] = $label;
        }
        $shifted[$sort] = $p->name.' ('.($year ?? '????').')';
        ksort($shifted);
        $this->positioned = $shifted;

        foreach ($this->cohorts as $y => $span) {
            $this->cohorts[$y] = [
                'min' => $span['min'] >= $sort ? $span['min'] + 1 : $span['min'],
                'max' => $span['max'] >= $sort ? $span['max'] + 1 : $span['max'],
            ];
        }

        if ($year !== null) {
            $this->cohorts[$year] = [
                'min' => min($this->cohorts[$year]['min'] ?? PHP_INT_MAX, $sort),
                'max' => max($this->cohorts[$year]['max'] ?? -1, $sort),
            ];
        }
    }

    /** The entries either side of a position, so a placement can be eyeballed. */
    private function neighboursOf(int $sort): array
    {
        $above = null;
        $below = null;

        foreach ($this->positioned as $s => $label) {
            if ($s < $sort) {
                $above = $label;
            } elseif ($below === null) {
                $below = $label;
            }
        }

        return [$above ?? '(top of the list)', $below ?? '(bottom of the list)'];
    }

    /** After writing: no one left at zero, and no two records sharing a position. */
    private function verify(): void
    {
        $left = Prisoner::withoutGlobalScopes()->where('sort_order', 0)->count();
        $total = Prisoner::withoutGlobalScopes()->count();
        $distinct = Prisoner::withoutGlobalScopes()->distinct()->count('sort_order');

        $this->newLine();
        $this->line("Still at sort_order 0: {$left}");
        $this->line("Records: {$total}   distinct sort_order values: {$distinct}");

        if ($distinct !== $total) {
            $this->warn('Those do not match — some records share a sort_order. Positions collided.');
        } else {
            $this->info('Every record holds a unique position.');
        }
    }
}
