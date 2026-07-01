<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Sets month-precision birthdates for the 23 March 5, 2023 Cop City defendants
 * whose DOBs appear on the DeKalb County Sheriff's Office booking photo advisory.
 */
class UpdateCopCityBirthdates extends Command {
    protected $signature   = 'prisoners:update-cop-city-birthdates';
    protected $description = 'Set month-precision birthdates for the 23 March 5 Cop City defendants from booking photos';

    public function handle(): int {
        $entries = [
            ['slug' => 'amin-chaoui',          'year' => 1991, 'month' => 9],
            ['slug' => 'ayla-king',             'year' => 2004, 'month' => 4],
            ['slug' => 'colin-dorsey',          'year' => 1980, 'month' => 11],
            ['slug' => 'dimitri-leny',          'year' => 1997, 'month' => 11],
            ['slug' => 'ehret-nottingham',      'year' => 2000, 'month' => 10],
            ['slug' => 'emma-bogush',           'year' => 1998, 'month' => 5],
            ['slug' => 'grace-martin',          'year' => 2000, 'month' => 4],
            ['slug' => 'jack-beaman',           'year' => 2000, 'month' => 10],
            ['slug' => 'james-marsicano',       'year' => 1993, 'month' => 3],
            ['slug' => 'kamryn-pipes',          'year' => 1996, 'month' => 1],
            ['slug' => 'kayley-meissner',       'year' => 2003, 'month' => 4],
            ['slug' => 'luke-harper',           'year' => 1995, 'month' => 10],
            ['slug' => 'maggie-gates',          'year' => 1997, 'month' => 12],
            ['slug' => 'mattia-luini',          'year' => 1992, 'month' => 9],
            ['slug' => 'max-biederman',         'year' => 1997, 'month' => 9],
            ['slug' => 'priscilla-grim',        'year' => 1974, 'month' => 3],
            ['slug' => 'frederique-robert-paul','year' => 1988, 'month' => 6],
            ['slug' => 'samuel-ward',           'year' => 1997, 'month' => 2],
            ['slug' => 'thomas-jurgens',        'year' => 1995, 'month' => 2],
            ['slug' => 'timothy-bilodeau',      'year' => 1997, 'month' => 7],
            ['slug' => 'victor-puertas',        'year' => 1976, 'month' => 11],
            ['slug' => 'zoe-larmey',            'year' => 1997, 'month' => 8],
            ['slug' => 'alexis-papali',         'year' => 1974, 'month' => 5],
        ];

        $updated = 0;
        foreach ($entries as $entry) {
            $prisoner = Prisoner::withoutGlobalScopes()->where('slug', $entry['slug'])->first();

            if (! $prisoner) {
                $this->warn("Not found: {$entry['slug']}");
                continue;
            }

            $prisoner->setPartialDate('birthdate', $entry['year'], $entry['month']);
            $prisoner->save();
            $updated++;
            $this->info("Updated {$prisoner->name}: " . $prisoner->formatPartialDate('birthdate'));
        }

        $this->info("Done. {$updated}/" . count($entries) . ' birthdates set.');

        return self::SUCCESS;
    }
}
