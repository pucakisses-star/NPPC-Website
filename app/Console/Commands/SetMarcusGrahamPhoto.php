<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches a portrait for the anarchist editor Marcus Graham — scanned from a
 * book page (historyiswhat.noblogs.org), with the caption, page number, and
 * facing-page bleed-through cropped out. The image is non-free/fair-use (see
 * CREDITS-nonfree.md). Only sets the photo when the prisoner currently has none.
 */
class SetMarcusGrahamPhoto extends Command
{
    protected $signature = 'prisoners:set-marcus-graham-photo';

    protected $description = 'Attach the cropped portrait of anarchist editor Marcus Graham';

    public function handle(): int
    {
        $src = database_path('data/photos/nonfree/marcus-graham.jpg');
        if (! is_file($src)) {
            $this->error('Source image not found: '.$src);

            return self::FAILURE;
        }

        $prisoner = Prisoner::withUnderReview()->where('name', 'Marcus Graham')->first();
        if (! $prisoner) {
            $this->warn('Marcus Graham not found — run prisoners:add-anarchist-press-prisoners first.');

            return self::SUCCESS;
        }

        if (! empty($prisoner->photo)) {
            $this->info('Marcus Graham already has a photo — leaving alone.');

            return self::SUCCESS;
        }

        Storage::disk('public')->makeDirectory('prisoners');
        $relative = 'prisoners/marcus-graham.jpg';
        Storage::disk('public')->put($relative, file_get_contents($src));
        $prisoner->photo = $relative;
        $prisoner->save();

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Linked photo for Marcus Graham.');

        return self::SUCCESS;
    }
}
