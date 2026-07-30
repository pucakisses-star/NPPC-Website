<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Find case rows whose custody ends before it starts.
 *
 * A release date earlier than the incarceration date is not a short
 * sentence. It is a mismatched pair: one row holding dates from two
 * different episodes. Lifelong activists collect many arrests -- the
 * Berrigans, James Lawson, the Plowshares defendants -- and a single case
 * row cannot hold two of them, so an importer that filled the arrest from
 * one episode and the release from another leaves the row internally
 * impossible.
 *
 * WHY THIS IS WORTH ITS OWN COMMAND. Until PrisonerCase::computeImprisonedForDays()
 * was guarded, every one of these published a figure, because diffInDays()
 * is absolute: Paul Magno's 2013 arrest against a 1986 release rendered on
 * his public profile as "Imprisoned For 27 years 10 months 10 days". The
 * guard suppresses the number, which stops the site asserting something
 * false -- but it does not repair the row, and a suppressed counter is
 * invisible. This command keeps the list visible and sorted by how badly
 * the pair disagrees.
 *
 * The two shapes need different handling, so they are reported separately:
 *
 *   DAYS APART -- almost certainly a data-entry slip in one of the two
 *   dates (a transposition, or a release recorded the day before intake).
 *   Usually fixable by inspection.
 *
 *   YEARS APART -- two different episodes in one row. Needs research to
 *   decide which dates belong together, and often a second case row.
 *
 * Read-only. It writes nothing; the point is the worklist.
 */
final class AuditInvertedCaseDates extends Command
{
    protected $signature = 'prisoners:audit-inverted-case-dates
        {--threshold=90 : Days apart above which a pair is treated as two different episodes rather than a slip}
        {--limit=0 : Show at most this many rows per section (0 = all)}';

    protected $description = 'Report case rows where the release precedes the incarceration (or the exile ends before it begins)';

    public function handle(): int
    {
        $threshold = max(1, (int) $this->option('threshold'));
        $limit = (int) $this->option('limit');

        $custody = [];
        $exile = [];

        Prisoner::withoutGlobalScopes()
            ->with('cases')
            ->chunk(200, function ($prisoners) use (&$custody, &$exile) {
                foreach ($prisoners as $prisoner) {
                    foreach ($prisoner->cases as $case) {
                        // setRelation so the guarded compute methods below see
                        // the parent without re-querying through the
                        // not-under-review global scope, which would hand back
                        // null for an under-review prisoner.
                        $case->setRelation('prisoner', $prisoner);

                        if ($gap = $this->gap($case->incarceration_date, $case->release_date)) {
                            $custody[] = [$prisoner, $case, $gap];
                        }

                        if ($gap = $this->gap($case->in_exile_since, $case->end_of_exile)) {
                            $exile[] = [$prisoner, $case, $gap];
                        }
                    }
                }
            });

        $this->section(
            'CUSTODY — release before incarceration',
            $custody,
            $threshold,
            $limit,
            fn (PrisonerCase $c) => ['in' => $c->incarceration_date, 'out' => $c->release_date],
        );

        $this->section(
            'EXILE — end before start',
            $exile,
            $threshold,
            $limit,
            fn (PrisonerCase $c) => ['in' => $c->in_exile_since, 'out' => $c->end_of_exile],
        );

        $total = count($custody) + count($exile);

        $this->newLine();

        if ($total === 0) {
            $this->info('No inverted date pairs. Every case ends on or after the day it starts.');

            return self::SUCCESS;
        }

        $fabricated = array_sum(array_map(fn ($r) => $r[2], array_merge($custody, $exile)));

        $this->warn("{$total} inverted pair(s).");
        $this->line('  Days that would be published as duration if the model guard were removed: '
            .number_format($fabricated)
            .' (~'.number_format($fabricated / 365.25, 0).' years)');
        $this->line('  The guard in PrisonerCase suppresses these, so the counters read as absent, not wrong.');

        return self::SUCCESS;
    }

    /** Positive number of days by which $end precedes $start, or null when the pair is fine. */
    private function gap($start, $end): ?int
    {
        if (! $start || ! $end) {
            return null;
        }

        $s = Carbon::parse($start);
        $e = Carbon::parse($end);

        return $e->lt($s) ? (int) $e->diffInDays($s) : null;
    }

    /** @param  array<int, array{0: Prisoner, 1: PrisonerCase, 2: int}>  $rows */
    private function section(string $title, array $rows, int $threshold, int $limit, callable $dates): void
    {
        $this->newLine();
        $this->line("<info>{$title}</info>  (".count($rows).')');

        if (! $rows) {
            $this->line('  none');

            return;
        }

        usort($rows, fn ($a, $b) => $b[2] <=> $a[2]);

        $episodes = array_values(array_filter($rows, fn ($r) => $r[2] > $threshold));
        $slips = array_values(array_filter($rows, fn ($r) => $r[2] <= $threshold));

        $this->group("Two different episodes in one row (> {$threshold} days apart) — needs research", $episodes, $limit, $dates);
        $this->group("Likely a data-entry slip (<= {$threshold} days apart) — fixable by inspection", $slips, $limit, $dates);
    }

    /** @param  array<int, array{0: Prisoner, 1: PrisonerCase, 2: int}>  $rows */
    private function group(string $heading, array $rows, int $limit, callable $dates): void
    {
        $this->newLine();
        $this->line("  <comment>{$heading}</comment>  (".count($rows).')');

        if (! $rows) {
            $this->line('    none');

            return;
        }

        $shown = $limit > 0 ? array_slice($rows, 0, $limit) : $rows;

        foreach ($shown as [$prisoner, $case, $gap]) {
            $d = $dates($case);
            $this->line(sprintf(
                '    %-34s  %6dd earlier   start=%s  end=%s  cases=%d',
                mb_strimwidth($prisoner->slug, 0, 34),
                $gap,
                $d['in'] ? $d['in']->toDateString() : '----------',
                $d['out'] ? $d['out']->toDateString() : '----------',
                $prisoner->cases->count(),
            ));

            if ($case->charges) {
                $this->line('        '.mb_strimwidth((string) $case->charges, 0, 92, '...'));
            }
        }

        if ($limit > 0 && count($rows) > $limit) {
            // Never let a --limit hide rows silently: a truncated list reads
            // as a complete one.
            $this->line('    ... '.(count($rows) - $limit).' more not shown (raise --limit)');
        }
    }
}
