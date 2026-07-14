<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Sets the imprisonment window on the schooner Pearl cases for Daniel Drayton
 * and Edward Sayres: incarcerated April 18, 1848 and released (pardoned)
 * August 11, 1852 — the roughly four-and-a-half-year term they served in the
 * Washington, D.C. jail. Also fills imprisoned_for_days to match. Idempotent.
 */
final class SetPearlImprisonmentDates extends Command
{
    protected $signature = 'prisoners:set-pearl-imprisonment-dates';

    protected $description = 'Set the Apr 18, 1848 – Aug 11, 1852 imprisonment window on the Drayton and Sayres cases';

    private const START = '1848-04-18';

    private const END = '1852-08-11';

    public function handle(): int
    {
        $days = (int) Carbon::parse(self::START)->diffInDays(Carbon::parse(self::END));

        foreach (['daniel-drayton', 'edward-sayres'] as $slug) {
            $prisoner = Prisoner::withUnderReview()->where('slug', $slug)->first();
            if (! $prisoner) {
                $this->warn("Not found: {$slug}");

                continue;
            }

            $case = $prisoner->cases()->first();
            if (! $case) {
                $this->warn("No case for {$prisoner->name}");

                continue;
            }

            $case->setPartialDate('incarceration_date', 1848, 4, 18);
            $case->setPartialDate('release_date', 1852, 8, 11);
            $case->imprisoned_for_days = $days;
            $case->save();

            $this->info("{$prisoner->name}: imprisoned Apr 18, 1848 – Aug 11, 1852 ({$days} days).");
        }

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Done.');

        return self::SUCCESS;
    }
}
