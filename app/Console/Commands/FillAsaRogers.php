<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Updates Asa Rogers — the aged Loudoun County, Virginia militia officer held as
 * a civilian political prisoner at Fort Delaware during the Civil War — with his
 * race, birth and death dates (June 4, 1802 – September 1, 1887, per his
 * Wikipedia article), and his public-domain 19th-century portrait (cropped from
 * its oval mount). Race is not stated on Wikipedia but is unambiguous from his
 * biography (an 1802-born Loudoun County plantation owner and militia general).
 * Idempotent.
 */
final class FillAsaRogers extends Command
{
    protected $signature = 'prisoners:fill-asa-rogers';

    protected $description = 'Set Asa Rogers\'s race, birth/death dates, and portrait';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'Asa Rogers')->first();
        if (! $prisoner) {
            $this->error('Asa Rogers not found.');

            return self::FAILURE;
        }

        $prisoner->race = 'White';
        $prisoner->setPartialDate('birthdate', 1802, 6, 4);
        $prisoner->setPartialDate('death_date', 1887, 9, 1);
        $prisoner->save();
        $this->info('Set race (White) and dates (b. Jun 4, 1802 – d. Sep 1, 1887).');

        $src = database_path('data/photos/asa-rogers.jpg');
        if (is_file($src)) {
            Storage::disk('public')->makeDirectory('prisoners');
            Storage::disk('public')->put('prisoners/asa-rogers.jpg', (string) file_get_contents($src));
            $prisoner->photo = 'prisoners/asa-rogers.jpg';
            $prisoner->save();
            $this->info('Attached portrait: prisoners/asa-rogers.jpg');
        } else {
            $this->warn('Portrait file not found — dates/race set, photo skipped.');
        }

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
