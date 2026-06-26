<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Sets Fulani Sunni-Ali's (born Cynthia Boston, 1948) date of birth and death:
 * birth year 1948 (year precision — only the year is known) and death July 17,
 * 2016 (full date). Idempotent; matches the live record by slug/name.
 */
final class SetFulaniSunniAliDates extends Command
{
    protected $signature = 'prisoners:set-fulani-dates';

    protected $description = 'Set Fulani Sunni-Ali date of birth (1948) and death (July 17, 2016)';

    public function handle(): int
    {
        $p = Prisoner::withoutGlobalScopes()
            ->where('slug', 'fulani-sunni-ali')
            ->orWhere('name', 'like', '%Sunni-Ali%')
            ->orWhere('name', 'like', '%Fulani%')
            ->first();

        if (! $p) {
            $this->warn('Fulani Sunni-Ali not found — nothing to update.');

            return self::SUCCESS;
        }

        $p->setPartialDate('birthdate', 1948);       // year only
        $p->setPartialDate('death_date', 2016, 7, 17); // full date
        $p->save();

        $this->info("Updated {$p->name}: born ".$p->formatPartialDate('birthdate').
            ', died '.$p->formatPartialDate('death_date')." (age {$p->age}). View: /prisoner/{$p->slug}");

        return self::SUCCESS;
    }
}
