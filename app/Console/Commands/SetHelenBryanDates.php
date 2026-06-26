<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Records Helen R. Bryan's birth and death. Her New York Times obituary ran on
 * September 11, 1976 and gives her age as 82; combined with her Wellesley
 * College class of 1917, that puts her birth at 1894 (1894 → Sept 1976 = 82,
 * matching the obituary). The exact day-level dates were not accessible from
 * here (the NYT archive is paywalled), so birth is recorded at year precision
 * and death at month precision. Idempotent.
 */
final class SetHelenBryanDates extends Command
{
    protected $signature = 'prisoners:set-helen-bryan-dates';

    protected $description = "Set Helen R. Bryan's birth (1894) and death (September 1976)";

    public function handle(): int
    {
        $p = Prisoner::withoutGlobalScopes()->where('slug', 'helen-bryan')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Helen%Bryan%')->first();

        if (! $p) {
            $this->error('No Helen Bryan record found.');

            return self::FAILURE;
        }

        $p->setPartialDate('birthdate', 1894);      // age 82 at her Sept 1976 death; Wellesley class of 1917
        $p->setPartialDate('death_date', 1976, 9);  // NYT obituary dated September 11, 1976
        $p->save();

        $this->info("Set Helen R. Bryan: birth 1894, death September 1976 (computed age {$p->age}).");
        $this->info("View: /prisoner/{$p->slug}");

        return self::SUCCESS;
    }
}
