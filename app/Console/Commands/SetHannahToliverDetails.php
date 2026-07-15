<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Hannah Toliver's birth and death dates (27 Dec 1805 – 24 Feb 1874, per
 * her Find a Grave memorial #37096669) and attaches her portrait if the image
 * file has been placed at database/data/photos/hannah-toliver.jpg. The portrait
 * is public domain (the subject died in 1874). Idempotent.
 */
final class SetHannahToliverDetails extends Command
{
    protected $signature = 'prisoners:set-hannah-toliver-details';

    protected $description = 'Set Hannah Toliver birth/death dates and attach her portrait';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'Hannah Toliver')->first();
        if (! $prisoner) {
            $this->error('Hannah Toliver not found.');

            return self::FAILURE;
        }

        $prisoner->setPartialDate('birthdate', 1805, 12, 27);
        $prisoner->setPartialDate('death_date', 1874, 2, 24);
        $prisoner->save();
        $this->info('Set dates: b. 27 Dec 1805, d. 24 Feb 1874.');

        $src = database_path('data/photos/hannah-toliver.jpg');
        if (is_file($src)) {
            if (empty($prisoner->photo)) {
                Storage::disk('public')->makeDirectory('prisoners');
                Storage::disk('public')->put('prisoners/hannah-toliver.jpg', file_get_contents($src));
                $prisoner->photo = 'prisoners/hannah-toliver.jpg';
                $prisoner->save();
                $this->info('Attached portrait: prisoners/hannah-toliver.jpg');
            } else {
                $this->info('Portrait already set; left as-is.');
            }
        } else {
            $this->warn('Portrait file not found at database/data/photos/hannah-toliver.jpg — dates set, photo skipped.');
        }

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
