<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Records birth years for the pro-life FACE Act prisoners profiled in the
 * NCRegister/CNA article "Meet the Pro-Life Prisoners Whom Trump Is Expected to
 * Pardon" (published 2024-11-21), derived from the ages the article listed:
 * birth year = 2024 − (age as of the article). Stored as a year-only partial
 * birthdate so the profile shows just the year and the age auto-computes.
 * Idempotent.
 */
final class SetProLifePrisonerAges extends Command
{
    protected $signature = 'prisoners:set-prolife-ages';

    protected $description = 'Set birth years for the pro-life prisoners from their ages in the NCRegister article';

    private const PUB_YEAR = 2024; // article published 2024-11-21

    /** prisoner slug => age listed in the article */
    private const AGES = [
        'lauren-handy' => 30,
        'john-hinshaw' => 69,
        'joan-bell' => 76,
        'jean-marshall' => 74,
        'paula-harlow' => 75,
        'eva-edl' => 89,
        'bevelyn-beatty-williams' => 33,
        'jonathan-darnel' => 42,
    ];

    public function handle(): int
    {
        $set = 0;

        foreach (self::AGES as $slug => $age) {
            $prisoner = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $prisoner) {
                $this->warn("No prisoner with slug '{$slug}' — skipped.");

                continue;
            }

            $year = self::PUB_YEAR - $age;
            $prisoner->setPartialDate('birthdate', $year);
            $prisoner->save();
            $this->info("{$prisoner->name}: birth year {$year} (age {$age} as of 2024-11-21)");
            $set++;
        }

        $this->info("\nDone. Set birth year on {$set} prisoner(s).");

        return self::SUCCESS;
    }
}
