<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds Morton Sobell's 1950 stint as a fugitive in Mexico to his espionage
 * case. A co-defendant in the Rosenberg atomic-espionage case, Sobell fled to
 * Mexico City on June 22, 1950; he was seized there and forcibly returned to the
 * United States on August 16, 1950, then tried, convicted, and sentenced to 30
 * years. The June 22 → August 16, 1950 window is recorded as his time in exile.
 *
 * Sets in_exile = true and currently_in_exile = false. Idempotent.
 */
final class SetSobellExileDates extends Command
{
    protected $signature = 'prisoners:set-sobell-exile-dates';

    protected $description = "Add Morton Sobell's 1950 time as a fugitive in Mexico (June 22 – Aug 16, 1950)";

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'Morton Sobell')->first();

        if (! $prisoner) {
            $this->warn('Morton Sobell not found, nothing to do.');

            return self::SUCCESS;
        }

        $prisoner->in_exile = true;
        $prisoner->currently_in_exile = false;
        $prisoner->save();

        $case = $prisoner->cases->first();
        if (! $case) {
            $this->warn('Morton Sobell has no case to update.');

            return self::SUCCESS;
        }

        $case->in_exile_since = '1950-06-22';
        $case->end_of_exile = '1950-08-16';
        $case->save();
        $case->refresh();

        $this->info(sprintf(
            'Sobell exile: %s → %s (%d days).',
            $case->in_exile_since?->format('Y-m-d'),
            $case->end_of_exile?->format('Y-m-d'),
            $case->in_exile_for_days ?? 0,
        ));

        return self::SUCCESS;
    }
}
