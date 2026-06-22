<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Corrects Pete O'Neal's birthdate, which was stored one day early (December 31
 * of the prior year) — the classic date-only timezone off-by-one, where a
 * midnight "January 1" entered through the admin gets rolled back a day. His
 * intended birthday is January 1, 1940 (year-only; his documented birth year is
 * 1940). Written directly here (server-side, in the app's UTC timezone) so the
 * stored value is exactly 1940-01-01 and the public profile renders Jan 1.
 *
 * Idempotent: does nothing if the date is already correct.
 */
final class FixPeteONealBirthdate extends Command
{
    protected $signature = 'prisoners:fix-pete-oneal-birthdate';

    protected $description = 'Correct Pete O\'Neal\'s birthdate to 1940-01-01 (was stored a day early as Dec 31)';

    private const BIRTHDATE = '1940-01-01';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'like', '%Pete O\'Neal%')->first();

        if (! $prisoner) {
            $this->warn('Pete O\'Neal not found, nothing to do.');

            return self::SUCCESS;
        }

        if ($prisoner->birthdate && $prisoner->birthdate->format('Y-m-d') === self::BIRTHDATE) {
            $this->line("Birthdate already correct for {$prisoner->name} (".self::BIRTHDATE.').');

            return self::SUCCESS;
        }

        $was = $prisoner->birthdate ? $prisoner->birthdate->format('Y-m-d') : '(none)';
        $prisoner->birthdate = self::BIRTHDATE;
        $prisoner->save();

        $this->info("Corrected {$prisoner->name} birthdate: {$was} → ".self::BIRTHDATE.' (January 1, 1940).');

        return self::SUCCESS;
    }
}
