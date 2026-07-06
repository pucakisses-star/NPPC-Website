<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches a 1975-era press portrait of Jill Raymond — the member of the
 * Lexington Six who spent roughly fourteen months in jail for refusing to
 * testify before a federal grand jury. The image is non-free/fair-use (see
 * CREDITS-nonfree.md). Only sets the photo when she currently has none.
 */
class SetJillRaymondPhoto extends Command
{
    protected $signature = 'prisoners:set-jill-raymond-photo';

    protected $description = 'Attach the 1975 press portrait of Jill Raymond (the Lexington Six)';

    public function handle(): int
    {
        $src = database_path('data/photos/nonfree/jill-raymond.jpg');
        if (! is_file($src)) {
            $this->error('Source image not found: '.$src);

            return self::FAILURE;
        }

        $prisoner = Prisoner::withUnderReview()->where('name', 'Jill Raymond')->first();
        if (! $prisoner) {
            $this->warn('Jill Raymond not found.');

            return self::SUCCESS;
        }

        if (! empty($prisoner->photo)) {
            $this->info('Jill Raymond already has a photo — leaving alone.');

            return self::SUCCESS;
        }

        Storage::disk('public')->makeDirectory('prisoners');
        $relative = 'prisoners/jill-raymond.jpg';
        Storage::disk('public')->put($relative, file_get_contents($src));
        $prisoner->photo = $relative;
        $prisoner->save();

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Linked photo for Jill Raymond.');

        return self::SUCCESS;
    }
}
