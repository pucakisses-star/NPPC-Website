<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Sets Hugh Dempsey's release and incarceration dates. Dempsey — Knights of
 * Labor District Master Workman convicted in the 1892 Homestead Strike
 * "poisoning" case — was pardoned and released on January 31, 1896 (after the
 * state's key witness recanted), having served roughly 3 years of a 7-year
 * term at the Western Penitentiary of Pennsylvania.
 *
 * His incarceration date is anchored to his November 15, 1892 sentencing (the
 * date already recorded on the case), when he began the term. Note: sources
 * differ on the conviction date — some place the verdict in mid-January 1893 —
 * so the prison-entry date carries a ~2-month uncertainty; the exact intake
 * date at the Western Penitentiary is not documented in accessible sources.
 *
 * Idempotent.
 */
final class SetDempseyDates extends Command
{
    protected $signature = 'prisoners:set-dempsey-dates';

    protected $description = "Set Hugh Dempsey's release (1896-01-31) and incarceration (1892-11-15) dates";

    public function handle(): int
    {
        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'hugh-dempsey')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'Hugh Dempsey')->first();

        if (! $prisoner) {
            $this->warn('No Hugh Dempsey record found.');

            return self::SUCCESS;
        }

        $prisoner->released = true;
        $prisoner->in_custody = false;
        $prisoner->save();

        $case = $prisoner->cases()->first();
        if (! $case) {
            $this->warn('Hugh Dempsey has no case to update.');

            return self::SUCCESS;
        }

        // Clear any stale year-only precision on these fields so the full dates render.
        $precision = $case->date_precision ?? [];
        unset($precision['incarceration_date'], $precision['release_date']);
        $case->date_precision = $precision ?: null;

        $case->incarceration_date = '1892-11-15'; // began term at sentencing
        $case->release_date = '1896-01-31';       // pardoned/released
        $case->save();
        $case->refresh();

        $this->info("Hugh Dempsey: incarcerated 1892-11-15 → released 1896-01-31 ({$case->imprisoned_for_days} days). View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
