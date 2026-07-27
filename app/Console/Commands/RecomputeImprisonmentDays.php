<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Support\ImprisonmentDuration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Recompute the stored imprisoned_for_days / in_exile_for_days columns.
 *
 * Both are written only by PrisonerCase::saving, so they are refreshed only
 * when the case row itself is saved. Two things routinely make them stale:
 *
 *   1. An open-ended case (incarceration date, no release date) counts to
 *      *today*, so its correct value grows every day. The stored number is
 *      frozen at whenever the row was last written — a case last saved years
 *      ago keeps reporting the span as of that day.
 *
 *   2. Whether an open-ended case counts at all depends on the *prisoner's*
 *      in_custody / awaiting_trial flags. Toggling those in the admin, or in
 *      a data script, changes what the case should hold without touching the
 *      case, so the old value survives.
 *
 * The profile page sums this column across a prisoner's cases and renders it
 * as "Time Imprisoned", so a stale row shows up directly as a wrong headline
 * figure.
 *
 * Dry by default. --slug prints a full per-case dump plus the exact total and
 * calendar breakdown the profile page would render, which is the quickest way
 * to see whether a suspect counter is bad data or bad display.
 */
final class RecomputeImprisonmentDays extends Command
{
    protected $signature = 'prisoners:recompute-imprisonment
        {--slug= : Limit to one prisoner, and print the full profile-page math}
        {--apply : Write the corrected values (default is a dry run)}';

    protected $description = 'Recompute stored imprisoned_for_days / in_exile_for_days and report anything stale';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $slug = $this->option('slug');

        $prisoners = Prisoner::withoutGlobalScopes()
            ->when($slug, fn ($q) => $q->where('slug', $slug))
            ->with('cases')
            ->get();

        if ($slug && $prisoners->isEmpty()) {
            $this->error("No prisoner with slug: {$slug}");

            return self::FAILURE;
        }

        $changed = 0;
        $scanned = 0;

        foreach ($prisoners as $prisoner) {
            $rows = [];

            foreach ($prisoner->cases as $case) {
                $scanned++;

                // setRelation avoids a per-case query for the parent, and
                // matters for correctness too: the belongsTo would apply the
                // not-under-review global scope and hand back null for an
                // under-review prisoner, which would zero out the counters.
                $case->setRelation('prisoner', $prisoner);

                $wasDays = $case->imprisoned_for_days;
                $wasExile = $case->in_exile_for_days;
                $nowDays = $case->computeImprisonedForDays();
                $nowExile = $case->computeInExileForDays();

                $stale = $wasDays !== $nowDays || $wasExile !== $nowExile;
                $rows[] = compact('case', 'wasDays', 'nowDays', 'wasExile', 'nowExile', 'stale');

                if (! $stale) {
                    continue;
                }

                $changed++;

                if ($apply) {
                    $case->save();   // the saving hook calls the same two methods
                }
            }

            $this->report($prisoner, $rows, (bool) $slug);
        }

        if ($apply && $changed > 0) {
            Cache::forget(PrisonerApiController::cacheKey());
        }

        $this->newLine();
        $this->line("Scanned {$scanned} case(s) across ".$prisoners->count().' prisoner(s).');

        if ($changed === 0) {
            $this->info('Nothing stale — every stored duration already matches.');
        } elseif ($apply) {
            $this->info("Rewrote {$changed} case(s).");
        } else {
            $this->warn("{$changed} case(s) are stale. Re-run with --apply to write the corrected values.");
        }

        return self::SUCCESS;
    }

    /**
     * Print one prisoner. In whole-table mode only stale cases are listed; with
     * --slug everything is shown, including the summed total and calendar
     * breakdown the profile page renders, so the on-page figure can be traced
     * back to the rows that produce it.
     */
    private function report(Prisoner $prisoner, array $rows, bool $verbose): void
    {
        $interesting = $verbose ? $rows : array_filter($rows, fn ($r) => $r['stale']);
        if (! $interesting) {
            return;
        }

        $this->newLine();
        $this->line("<info>{$prisoner->name}</info>  [/prisoner/{$prisoner->slug}]");

        if ($verbose) {
            $this->line('  in_custody='.($prisoner->in_custody ? 'yes' : 'no')
                .'  awaiting_trial='.($prisoner->awaiting_trial ? 'yes' : 'no')
                .'  released='.($prisoner->released ? 'yes' : 'no')
                .'  currently_in_exile='.($prisoner->currently_in_exile ? 'yes' : 'no')
                .'  under_review='.($prisoner->under_review ? 'yes' : 'no'));
        }

        foreach ($interesting as $r) {
            /** @var PrisonerCase $case */
            $case = $r['case'];
            $marker = $r['stale'] ? '<comment>STALE</comment>' : '     ';
            $this->line('  '.$marker
                .'  arrest='.$this->d($case->arrest_date)
                .'  in='.$this->d($case->incarceration_date)
                .'  out='.$this->d($case->release_date)
                .'  days: '.$this->n($r['wasDays']).' -> '.$this->n($r['nowDays'])
                .($r['wasExile'] !== null || $r['nowExile'] !== null
                    ? '  exile: '.$this->n($r['wasExile']).' -> '.$this->n($r['nowExile'])
                    : ''));
            $this->line('           '.mb_strimwidth((string) $case->charges, 0, 96, '...'));
        }

        if (! $verbose) {
            return;
        }

        // Exactly what pages/prisoner.blade.php does: sum the column, anchor
        // to the earliest start date, break the span into calendar units.
        $stored = array_sum(array_map(fn ($r) => (int) $r['wasDays'], $rows));
        $fresh = array_sum(array_map(fn ($r) => (int) $r['nowDays'], $rows));
        $start = collect($rows)
            ->map(fn ($r) => $r['case']->incarceration_date ?: $r['case']->arrest_date)
            ->filter()->sort()->first();

        $this->newLine();
        $this->line('  Time Imprisoned counter, anchored at '.$this->d($start).':');
        $this->line('    as stored now:  '.$this->span($start, $stored)."   ({$stored} days)");
        $this->line('    recomputed:     '.$this->span($start, $fresh)."   ({$fresh} days)");

        if ($stored !== $fresh) {
            $this->warn('    The page is rendering the stored figure. Re-run with --apply.');
        }
    }

    private function span($start, int $days): string
    {
        if ($days <= 0) {
            return '(no counter shown)';
        }

        ['years' => $y, 'months' => $m, 'days' => $d] = ImprisonmentDuration::breakdown($start, $days);

        return trim(($y ? "{$y}y " : '').($m ? "{$m}m " : '')."{$d}d");
    }

    private function d($date): string
    {
        return $date ? $date->toDateString() : '----------';
    }

    private function n(?int $value): string
    {
        return $value === null ? 'null' : (string) $value;
    }
}
