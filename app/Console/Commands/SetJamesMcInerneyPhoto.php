<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches the public-domain 1920 Washington State Penitentiary mug-shot
 * (W.S.P. #9410, cropped to the frontal view) of James McInerney — one of the
 * eight IWW defendants convicted in the 1920 Centralia case — to his record.
 * Image over 100 years old (public domain); see CREDITS-wikipedia.md.
 * Idempotent — always refreshes the stored copy.
 */
final class SetJamesMcInerneyPhoto extends Command
{
    protected $signature = 'prisoners:set-james-mcinerney-photo';

    protected $description = 'Attach the prison mug-shot of James McInerney (1920 Centralia IWW case)';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'James McInerney')->first();
        if (! $prisoner) {
            $this->error('James McInerney not found.');

            return self::FAILURE;
        }

        $src = database_path('data/photos/james-mcinerney.jpg');
        if (! is_file($src)) {
            $this->error('Photo source not found: '.$src);

            return self::FAILURE;
        }

        Storage::disk('public')->makeDirectory('prisoners');
        Storage::disk('public')->put('prisoners/james-mcinerney.jpg', (string) file_get_contents($src));
        $prisoner->photo = 'prisoners/james-mcinerney.jpg';
        $prisoner->save();

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info("Set photo for {$prisoner->name} -> prisoners/james-mcinerney.jpg");

        return self::SUCCESS;
    }
}
