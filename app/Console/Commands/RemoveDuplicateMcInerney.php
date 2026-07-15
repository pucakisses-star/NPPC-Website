<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Removes the duplicate "John McInerney" record. The 1920 Centralia IWW
 * defendant is James McInerney (kept, with the fuller bio and his WSP mug-shot);
 * "John McInerney" was an incorrectly-named duplicate of the same man and has
 * also been removed from prisoners:add-pol-violence-deep so it will not be
 * re-added. Matched by name + the Centralia/IWW context to avoid touching any
 * unrelated John McInerney. Idempotent.
 */
final class RemoveDuplicateMcInerney extends Command
{
    protected $signature = 'prisoners:remove-duplicate-mcinerney';

    protected $description = 'Delete the duplicate "John McInerney" (same person as James McInerney)';

    public function handle(): int
    {
        $dup = Prisoner::withUnderReview()
            ->where('name', 'John McInerney')
            ->get()
            ->first(fn ($p) => str_contains((string) $p->description, 'Centralia')
                || in_array('Industrial Workers of the World', (array) $p->affiliation, true));

        if (! $dup) {
            $this->info('No duplicate John McInerney (Centralia) found — already removed.');

            return self::SUCCESS;
        }

        $dup->cases()->delete();
        $dup->delete();
        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Deleted duplicate: John McInerney (slug: '.$dup->slug.').');

        return self::SUCCESS;
    }
}
