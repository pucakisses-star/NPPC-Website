<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Removes the Clark Foreman entry. Foreman was cited for contempt of Congress
 * in 1956 for refusing to surrender his passport to HUAC but was not imprisoned,
 * so he does not belong in the political-prisoner database. He has also been
 * removed from database/data/ng-1956-c.json so the National Guardian 1956
 * loader will not re-add him. Idempotent.
 */
final class RemoveClarkForeman extends Command
{
    protected $signature = 'prisoners:remove-clark-foreman';

    protected $description = 'Delete the Clark Foreman entry (cited for contempt, never imprisoned)';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'Clark Foreman')->first();
        if (! $prisoner) {
            $this->info('Clark Foreman not found — already removed.');

            return self::SUCCESS;
        }

        $prisoner->cases()->delete();
        $prisoner->delete();
        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Deleted Clark Foreman.');

        return self::SUCCESS;
    }
}
