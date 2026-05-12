<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * The first run of archive:enrich-prisoners-from-wikipedia matched
 * "Frank Miller" to the comics writer's Wikipedia page and wrote his
 * birthdate (1957-01-27). Clear it.
 */
final class FixFrankMillerBirthdate extends Command {
    protected $signature = 'archive:fix-frank-miller-birthdate';
    protected $description = 'Clear the incorrectly-imported 1957-01-27 birthdate on the Frank Miller prisoner record';

    public function handle(): int {
        $p = Prisoner::where('name', 'Frank Miller')->first();
        if (! $p) {
            $this->warn('No prisoner named "Frank Miller" found.');

            return self::SUCCESS;
        }
        if ((string) $p->birthdate !== '1957-01-27') {
            $this->info("Frank Miller's birthdate is '{$p->birthdate}', not 1957-01-27 — leaving alone.");

            return self::SUCCESS;
        }
        $p->birthdate = null;
        $p->save();
        $this->info('Cleared Frank Miller birthdate.');

        return self::SUCCESS;
    }
}
