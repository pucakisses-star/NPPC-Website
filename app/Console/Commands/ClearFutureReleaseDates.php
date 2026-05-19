<?php

namespace App\Console\Commands;

use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Null out PrisonerCase.release_date values that fall in the future.
 *
 * Background:
 *   Prisoner::getIncarcerationYearsArray() builds each year between
 *   the case's start date and end date. The end date is
 *   `release_date ?? death_in_custody_date ?? Carbon::now()`. So any
 *   future scheduled release_date — a 30-year sentence that's not
 *   yet served, say — pulls the year-range into the future and makes
 *   the prisoner appear in years they haven't actually been in prison
 *   for yet. That's what produced the "Total Political Prisoners"
 *   chart running out to 2042.
 *
 * Fix:
 *   Treat any release_date > today as unknown (NULL). The model will
 *   then fall back to Carbon::now() as the end of the range — i.e.
 *   "still in custody, still counting today as the most recent year".
 *   The actual scheduled-release information was metadata, not a
 *   ground truth.
 *
 * Idempotent — re-runs are no-ops once future dates are cleared.
 * Pass --dry-run to see what would change without writing.
 */
final class ClearFutureReleaseDates extends Command {
    protected $signature = 'prisoners:clear-future-release-dates {--dry-run : Report rows that would be cleared, do not write}';
    protected $description = 'NULL out PrisonerCase.release_date values that are in the future (projected, not yet served)';

    public function handle(): int {
        $today = Carbon::today();
        $cases = PrisonerCase::query()
            ->whereNotNull('release_date')
            ->where('release_date', '>', $today)
            ->with('prisoner:id,name,slug')
            ->orderBy('release_date')
            ->get(['id', 'prisoner_id', 'release_date']);

        if ($cases->isEmpty()) {
            $this->info('No PrisonerCase rows have a release_date in the future. Nothing to do.');
            return self::SUCCESS;
        }

        $this->info("Found {$cases->count()} case(s) with future release_date:");
        foreach ($cases as $c) {
            $this->line(sprintf(
                '  %s  →  case %s  (prisoner %s%s)',
                $c->release_date->format('Y-m-d'),
                $c->id,
                $c->prisoner?->name ?? '?',
                $c->prisoner?->slug ? " / {$c->prisoner->slug}" : '',
            ));
        }

        if ($this->option('dry-run')) {
            $this->info('--dry-run: no changes written. Re-run without --dry-run to apply.');
            return self::SUCCESS;
        }

        $cleared = 0;
        foreach ($cases as $c) {
            $c->release_date = null;
            $c->save();
            $cleared++;
        }
        $this->info("Cleared release_date on {$cleared} case(s).");

        return self::SUCCESS;
    }
}
