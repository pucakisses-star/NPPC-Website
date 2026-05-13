<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Sets Casey Goonan's website + Instagram from the Free Casey Now
 * support campaign.
 */
final class UpdateCaseyGoonanSocials extends Command {
    protected $signature = 'archive:update-casey-goonan-socials';
    protected $description = 'Add Free Casey Now website + Instagram to Casey Goonan';

    public function handle(): int {
        $prisoner = Prisoner::withUnderReview()->where('slug', 'casey-goonan')->first();
        if (! $prisoner) {
            $this->error('Prisoner not found: casey-goonan');

            return self::FAILURE;
        }

        $changed = false;
        if (empty($prisoner->website)) {
            $prisoner->website = 'https://freecaseynow.noblogs.org/';
            $this->info('Set website = https://freecaseynow.noblogs.org/');
            $changed = true;
        }
        if (empty($prisoner->instagram)) {
            $prisoner->instagram = 'https://instagram.com/freecaseynow';
            $this->info('Set instagram = https://instagram.com/freecaseynow');
            $changed = true;
        }

        if ($changed) {
            $prisoner->save();
            $this->info('Saved.');
        } else {
            $this->info('Both fields already set; nothing to do.');
        }

        return self::SUCCESS;
    }
}
