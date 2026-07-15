<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Sets race = Asian for Tao Li. (The two-letter Chinese surname "Li" is below
 * the length threshold the general prisoners:infer-race scan uses, so this is
 * set explicitly.) Idempotent.
 */
final class SetTaoLiRace extends Command
{
    protected $signature = 'prisoners:set-tao-li-race';

    protected $description = 'Set race = Asian for Tao Li';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('slug', 'tao-li')->first();
        if (! $prisoner) {
            $this->error('Prisoner not found: tao-li');

            return self::FAILURE;
        }

        Prisoner::withUnderReview()->whereKey($prisoner->getKey())->update(['race' => 'Asian']);
        Cache::forget(PrisonerApiController::cacheKey());

        $this->info("Set race = Asian for {$prisoner->name}.");

        return self::SUCCESS;
    }
}
