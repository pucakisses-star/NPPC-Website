<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Removes the David Rabkin entry (South African anti-apartheid journalist).
 * Idempotent — safe to re-run.
 */
final class RemoveDavidRabkin extends Command
{
    protected $signature = 'prisoners:remove-david-rabkin';

    protected $description = 'Delete the David Rabkin entry';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'David Rabkin')->first();
        if (! $prisoner) {
            $this->info('David Rabkin not found — already removed.');

            return self::SUCCESS;
        }

        $prisoner->cases()->delete();
        $prisoner->delete();
        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Deleted David Rabkin.');

        return self::SUCCESS;
    }
}
