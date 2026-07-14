<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Rose Pastor Stokes — socialist orator convicted under the 1918 Espionage Act
 * for her Kansas City Star letter. Sets her actual jailing window (she was held
 * only March 22–26, 1918, then freed on bail; her conviction was reversed on
 * appeal in 1920, so she never served the ten-year sentence) and attaches a
 * public-domain c.1918 NARA portrait if she has none. Idempotent.
 */
final class SetRosePastorStokes extends Command
{
    protected $signature = 'prisoners:set-rose-pastor-stokes';

    protected $description = 'Set Rose Pastor Stokes\'s March 1918 jailing dates and attach her portrait';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('slug', 'rose-pastor-stokes')->first();
        if (! $prisoner) {
            $this->error('Prisoner not found: rose-pastor-stokes');

            return self::FAILURE;
        }

        // Jailing window: March 22–26, 1918 (4 days).
        $case = $prisoner->cases()->first();
        if ($case) {
            $case->setPartialDate('incarceration_date', 1918, 3, 22);
            $case->setPartialDate('release_date', 1918, 3, 26);
            $case->imprisoned_for_days = 4;
            $case->save();
            $this->info('Case dates set: jailed March 22–26, 1918.');
        } else {
            $this->warn('No case found for Rose Pastor Stokes — dates not set.');
        }

        // Public-domain NARA portrait, only if she has no photo.
        if (empty($prisoner->photo)) {
            $src = database_path('data/photos/rose-pastor-stokes.jpg');
            if (is_file($src)) {
                Storage::disk('public')->makeDirectory('prisoners');
                Storage::disk('public')->put('prisoners/rose-pastor-stokes.jpg', (string) file_get_contents($src));
                $prisoner->photo = 'prisoners/rose-pastor-stokes.jpg';
                $prisoner->save();
                $this->info('Portrait attached.');
            } else {
                $this->warn('Portrait file missing: '.$src);
            }
        } else {
            $this->line('Already has a photo — leaving alone.');
        }

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Done: Rose Pastor Stokes updated.');

        return self::SUCCESS;
    }
}
