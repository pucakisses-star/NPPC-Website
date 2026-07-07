<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches the public-domain 1917–18 federal prisoner mugshot of Mortimer
 * Downing — IWW leader convicted at the 1918 Sacramento mass trial — to his
 * record. Image over 100 years old (public domain); see CREDITS-wikipedia.md.
 * Idempotent — always refreshes the stored copy.
 */
final class SetMortimerDowningPhoto extends Command
{
    protected $signature = 'prisoners:set-mortimer-downing-photo';

    protected $description = 'Attach the prisoner portrait of Mortimer Downing (Sacramento IWW trial)';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'Mortimer Downing')->first();
        if (! $prisoner) {
            $this->error('Mortimer Downing not found.');

            return self::FAILURE;
        }

        $src = database_path('data/photos/mortimer-downing.jpg');
        if (! is_file($src)) {
            $this->error('Photo source not found: '.$src);

            return self::FAILURE;
        }

        Storage::disk('public')->makeDirectory('prisoners');
        Storage::disk('public')->put('prisoners/mortimer-downing.jpg', (string) file_get_contents($src));
        $prisoner->photo = 'prisoners/mortimer-downing.jpg';
        $prisoner->save();

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info("Set photo for {$prisoner->name} -> prisoners/mortimer-downing.jpg");

        return self::SUCCESS;
    }
}
