<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds Eldridge Cleaver's time in exile to his 1968 Oakland case. Facing a
 * court-ordered return to prison after the April 6, 1968 shootout (he had been
 * freed on bail June 12, 1968), Cleaver jumped bail and fled the United States
 * in late November 1968, living in exile in Cuba, then Algeria, then France. He
 * returned to the U.S. in November 1975, flying into New York where the FBI
 * arrested him on landing — about seven years abroad.
 *
 * Day-level dates are not documented in accessible sources (every account gives
 * only "November 1968" and "November 1975"), so in_exile_since is stored as
 * 1968-11-24 and end_of_exile as 1975-11-18 — approximate, marking the ~7-year
 * span. Sets in_exile = true and currently_in_exile = false (he returned).
 * Idempotent.
 */
final class SetCleaverExileDates extends Command
{
    protected $signature = 'prisoners:set-cleaver-exile-dates';

    protected $description = "Add Eldridge Cleaver's 1968–1975 exile (Cuba/Algeria/France) to his Oakland case";

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'Eldridge Cleaver')->first();

        if (! $prisoner) {
            $this->warn('Eldridge Cleaver not found, nothing to do.');

            return self::SUCCESS;
        }

        $prisoner->in_exile = true;
        $prisoner->currently_in_exile = false;
        $prisoner->save();

        $case = $prisoner->cases->first();
        if (! $case) {
            $this->warn('Eldridge Cleaver has no case to update.');

            return self::SUCCESS;
        }

        $case->in_exile_since = '1968-11-24';
        $case->end_of_exile = '1975-11-18';
        $case->save();
        $case->refresh();

        $this->info(sprintf(
            'Cleaver exile: %s → %s (%d days ≈ %.1f years).',
            $case->in_exile_since?->format('Y-m-d'),
            $case->end_of_exile?->format('Y-m-d'),
            $case->in_exile_for_days ?? 0,
            ($case->in_exile_for_days ?? 0) / 365,
        ));

        return self::SUCCESS;
    }
}
