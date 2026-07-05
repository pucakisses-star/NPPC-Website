<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Fills in Paul Kleinbord — the Colorado Communist Party leader jailed for
 * contempt in Denver in 1948 — who was born Paul Meier Klienbordt and later,
 * after changing his name in the 1950s, was known as the artist Steve "Pablo"
 * Davis (a Detroit muralist who had worked with Diego Rivera on the Detroit
 * Industry frescoes; d. 2013). Per his Wikipedia article
 * (en.wikipedia.org/wiki/Steve_"Pablo"_Davis):
 *
 *   - date of birth: July 5, 1916  (the infobox date; the article body gives
 *     July 7, 1916 — the day is recorded here at DAY precision as July 5)
 *   - date of death: January 5, 2013 (aged 96)
 *
 * Also attaches his later-life portrait (committed under a fair-use / memorial
 * rationale in photos/nonfree/, credited in CREDITS-nonfree.md) and records his
 * "Steve \"Pablo\" Davis" alias. Idempotent / re-runnable.
 */
class SetKleinbordDetails extends Command
{
    protected $signature = 'prisoners:set-kleinbord-details';

    protected $description = 'Set Paul Kleinbord (Steve "Pablo" Davis) photo, date of birth, date of death, and alias';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'Paul Kleinbord')->first();

        if (! $prisoner) {
            $this->warn('Prisoner "Paul Kleinbord" not found — nothing to update.');

            return self::FAILURE;
        }

        // Portrait (non-free, fair-use — see CREDITS-nonfree.md).
        $src = database_path('data/photos/nonfree/paul-kleinbord.jpg');
        if (is_file($src)) {
            $path = 'prisoners/paul-kleinbord.jpg';
            Storage::disk('public')->makeDirectory('prisoners');
            Storage::disk('public')->put($path, (string) file_get_contents($src));
            $prisoner->photo = $path;
            $this->info('Photo set: '.$path);
        } else {
            $this->warn('Photo source not found: '.$src);
        }

        if (empty($prisoner->aka)) {
            $prisoner->aka = 'Steve "Pablo" Davis';
        }

        // Full documented dates, stored at day precision.
        $prisoner->setPartialDate('birthdate', 1916, 7, 5);
        $prisoner->setPartialDate('death_date', 2013, 1, 5);

        $prisoner->save();

        $this->info('Updated '.$prisoner->name
            .' — born '.$prisoner->formatPartialDate('birthdate')
            .', died '.$prisoner->formatPartialDate('death_date')
            .', aka '.$prisoner->aka);

        return self::SUCCESS;
    }
}
