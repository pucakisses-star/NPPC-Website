<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Fills in details for Per Herngren (aka "Pat Herngren"), the Swedish Pershing
 * Plowshares activist: clears the "Sweden" state (he is a Swedish national held
 * in the U.S., not a U.S. state), sets his date of birth (July 16, 1961, from
 * Swedish Wikipedia), his Facebook page, and his CC BY-SA 4.0 Wikimedia Commons
 * portrait (by Sofie Sigrinn; see CREDITS-wikipedia.md). Idempotent — always
 * refreshes these fields; the photo is (re)attached from the bundled file.
 */
final class UpdatePerHerngren extends Command
{
    protected $signature = 'prisoners:update-per-herngren';

    protected $description = 'Update Per Herngren: clear state, add DOB, Facebook, and Wikimedia portrait';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()
            ->where('name', 'Per Herngren')
            ->orWhere('aka', 'Pat Herngren')
            ->first();

        if (! $prisoner) {
            $this->error('Per Herngren not found.');

            return self::FAILURE;
        }

        $prisoner->state = null;
        $prisoner->facebook = 'https://www.facebook.com/perherngren/';
        $prisoner->setPartialDate('birthdate', 1961, 7, 16);

        $src = database_path('data/photos/per-herngren.jpg');
        if (is_file($src)) {
            Storage::disk('public')->makeDirectory('prisoners');
            Storage::disk('public')->put('prisoners/per-herngren.jpg', (string) file_get_contents($src));
            $prisoner->photo = 'prisoners/per-herngren.jpg';
        } else {
            $this->warn('Photo source not found: '.$src);
        }

        $prisoner->save();

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Updated Per Herngren (slug: '.$prisoner->slug.') — state cleared, DOB 1961-07-16, Facebook and photo set.');

        return self::SUCCESS;
    }
}
