<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Removes the Kim Dae-jung entry (stored as "Kim Dae Jung"). Idempotent —
 * safe to re-run; also matches the hyphenated name variant.
 */
final class RemoveKimDaeJung extends Command
{
    protected $signature = 'prisoners:remove-kim-dae-jung';

    protected $description = 'Delete the Kim Dae-jung (Kim Dae Jung) entry';

    public function handle(): int
    {
        $prisoners = Prisoner::withUnderReview()
            ->whereIn('name', ['Kim Dae Jung', 'Kim Dae-jung'])
            ->get();

        if ($prisoners->isEmpty()) {
            $this->info('Kim Dae-jung not found — already removed.');

            return self::SUCCESS;
        }

        foreach ($prisoners as $prisoner) {
            $prisoner->cases()->delete();
            $prisoner->delete();
            $this->info('Deleted: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
        }

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
