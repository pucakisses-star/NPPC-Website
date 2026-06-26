<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Records Helen R. Bryan's birth and death. Her New York Times obituary
 * (published Saturday, September 11, 1976) reports that she "died Thursday in
 * Medford Lakes, N.J., at the age of 82" — the Thursday before publication was
 * September 9, 1976. Her age of 82, with her Wellesley College class of 1917,
 * puts her birth at 1894 (1894 → 1976 = 82). Death is recorded to the day;
 * birth at year precision (the obituary gives no birth date). Idempotent.
 */
final class SetHelenBryanDates extends Command
{
    protected $signature = 'prisoners:set-helen-bryan-dates';

    protected $description = "Set Helen R. Bryan's birth (1894) and death (September 9, 1976)";

    public function handle(): int
    {
        $p = Prisoner::withoutGlobalScopes()->where('slug', 'helen-bryan')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Helen%Bryan%')->first();

        if (! $p) {
            $this->error('No Helen Bryan record found.');

            return self::FAILURE;
        }

        $p->setPartialDate('birthdate', 1894);         // age 82 at death; Wellesley class of 1917 (obituary gives no birth date)
        $p->setPartialDate('death_date', 1976, 9, 9);  // "died Thursday" before the Sat. Sept 11, 1976 obituary
        $p->save();

        $this->info("Set Helen R. Bryan: birth 1894, death September 9, 1976 (computed age {$p->age}).");
        $this->info("View: /prisoner/{$p->slug}");

        return self::SUCCESS;
    }
}
