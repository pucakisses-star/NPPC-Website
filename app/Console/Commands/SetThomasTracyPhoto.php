<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Refreshes Thomas H. Tracy's photo with a cleaner frontal crop of his 1916
 * Snohomish County jail booking photo (IWW Prisoner 4866), taken from the
 * Everett Public Library's "Everett Massacre Collection" digital archive
 * (nw.epls.org, item 30). Tracy was the one Everett Massacre defendant brought
 * to trial; his May 5, 1917 acquittal ended the prosecutions. The roster loader
 * only attaches a photo when the record has none, so this command force-updates
 * the stored copy. Image over 100 years old (public domain); see
 * CREDITS-wikipedia.md. Idempotent — always refreshes the stored copy.
 */
final class SetThomasTracyPhoto extends Command
{
    protected $signature = 'prisoners:set-thomas-tracy-photo';

    protected $description = 'Refresh Thomas H. Tracy\'s photo with a cleaner frontal crop of his 1916 booking photo';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'Thomas H. Tracy')->first();
        if (! $prisoner) {
            $this->error('Thomas H. Tracy not found.');

            return self::FAILURE;
        }

        $src = database_path('data/photos/thomas-h-tracy.jpg');
        if (! is_file($src)) {
            $this->error('Photo source not found: '.$src);

            return self::FAILURE;
        }

        Storage::disk('public')->makeDirectory('prisoners');
        Storage::disk('public')->put('prisoners/thomas-h-tracy.jpg', (string) file_get_contents($src));
        $prisoner->photo = 'prisoners/thomas-h-tracy.jpg';
        $prisoner->save();

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info("Set photo for {$prisoner->name} -> prisoners/thomas-h-tracy.jpg");

        return self::SUCCESS;
    }
}
