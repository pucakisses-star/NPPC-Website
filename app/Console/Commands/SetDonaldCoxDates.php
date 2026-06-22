<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Sets the birth and death dates for Donald "DC" Cox — Field Marshal of the
 * Black Panther Party — from his Wikipedia biography (born April 16, 1936 in
 * Appleton City, Missouri; died February 19, 2011 in Camps-sur-l'Agly, France).
 *
 * Variant-aware on the name and only fills dates that are currently empty
 * (never overwrites); idempotent.
 */
final class SetDonaldCoxDates extends Command
{
    protected $signature = 'prisoners:set-donald-cox-dates';

    protected $description = 'Set Donald "DC" Cox\'s birth and death dates from his Wikipedia biography';

    public function handle(): int
    {
        $prisoner = null;
        foreach (['Donald Cox', 'Don Cox'] as $fragment) {
            $prisoner = Prisoner::withUnderReview()->where('name', 'like', '%'.$fragment.'%')->first();
            if ($prisoner) {
                break;
            }
        }

        if (! $prisoner) {
            $this->warn('Donald Cox not found, nothing to do.');

            return self::SUCCESS;
        }

        $changed = false;
        if (! $prisoner->birthdate) {
            $prisoner->birthdate = '1936-04-16';
            $changed = true;
        }
        if (! $prisoner->death_date) {
            $prisoner->death_date = '2011-02-19';
            $changed = true;
        }

        if ($changed) {
            $prisoner->save();
            $this->info("Updated {$prisoner->name}: born {$prisoner->birthdate}, died {$prisoner->death_date}");
        } else {
            $this->line("Dates already set for {$prisoner->name}, nothing to do.");
        }

        return self::SUCCESS;
    }
}
