<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches a portrait for the labor organizer Martin Young — held without bail
 * at Ellis Island under the McCarran Act (1951–52) facing deportation. Cropped
 * from the "MARTIN YOUNG & SON" halftone accompanying his open letter in the
 * National Guardian, April 9, 1952, p. 6 (digitized at marxists.org). The image
 * is non-free/fair-use (see CREDITS-nonfree.md). Only sets the photo when the
 * prisoner currently has none. Idempotent.
 */
class SetMartinYoungPhoto extends Command
{
    protected $signature = 'prisoners:set-martin-young-photo {--overwrite : Replace an existing photo too}';

    protected $description = 'Attach the cropped 1952 National Guardian portrait of labor organizer Martin Young';

    public function handle(): int
    {
        $src = database_path('data/photos/nonfree/martin-young.jpg');
        if (! is_file($src)) {
            $this->error('Source image not found: '.$src);

            return self::FAILURE;
        }

        $prisoner = Prisoner::withUnderReview()->where('name', 'Martin Young')->first();
        if (! $prisoner) {
            $this->warn('Martin Young not found — run the National Guardian 1951 import first.');

            return self::SUCCESS;
        }

        if (! empty($prisoner->photo) && ! $this->option('overwrite')) {
            $this->info('Martin Young already has a photo — leaving alone (use --overwrite to replace).');

            return self::SUCCESS;
        }

        Storage::disk('public')->makeDirectory('prisoners');
        $relative = 'prisoners/martin-young.jpg';
        Storage::disk('public')->put($relative, (string) file_get_contents($src));
        $prisoner->photo = $relative;
        $prisoner->save();

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Linked photo for Martin Young (slug: '.$prisoner->slug.').');

        return self::SUCCESS;
    }
}
