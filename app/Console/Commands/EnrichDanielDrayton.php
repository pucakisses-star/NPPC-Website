<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Fills the missing vital dates and portrait on the existing Daniel Drayton
 * record — the captain of the schooner Pearl in the April 1848 mass escape
 * attempt from Washington, D.C., imprisoned for it until his 1852 pardon.
 *
 * - Birth: 1802 (year only). Death: July 1, 1857.
 * - Attaches the public-domain 1850s engraving (database/data/photos/
 *   daniel-drayton.jpg) if he has no photo. See CREDITS-daniel-drayton.md.
 *
 * Idempotent (skips the photo if one is already set).
 */
final class EnrichDanielDrayton extends Command
{
    protected $signature = 'prisoners:enrich-daniel-drayton';

    protected $description = 'Set Daniel Drayton\'s birth/death dates and attach his portrait';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('slug', 'daniel-drayton')->first();
        if (! $prisoner) {
            $this->error('Prisoner not found: daniel-drayton');

            return self::FAILURE;
        }

        $prisoner->setPartialDate('birthdate', 1802);          // year only
        $prisoner->setPartialDate('death_date', 1857, 7, 1);   // July 1, 1857
        $prisoner->save();
        $this->info('Dates set: b. 1802, d. July 1, 1857.');

        if (empty($prisoner->photo)) {
            $src = database_path('data/photos/daniel-drayton.jpg');
            if (is_file($src)) {
                Storage::disk('public')->makeDirectory('prisoners');
                Storage::disk('public')->put('prisoners/daniel-drayton.jpg', (string) file_get_contents($src));
                $prisoner->photo = 'prisoners/daniel-drayton.jpg';
                $prisoner->save();
                $this->info('Portrait attached.');
            } else {
                $this->warn('Portrait file missing: '.$src);
            }
        } else {
            $this->line('Already has a photo — leaving alone.');
        }

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Done: Daniel Drayton enriched.');

        return self::SUCCESS;
    }
}
