<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Removes Scott Warren — the No More Deaths volunteer arrested Jan 2018
 * in Ajo, AZ for harboring migrants. He was released on his own
 * recognizance, never served any jail time, and was ultimately acquitted
 * at his Nov 2019 retrial.
 *
 * He was added by an older AddRecentEraPrisoners command before the
 * project's "must have served jail time" criterion was applied.
 */
final class RemoveScottWarren extends Command {
    protected $signature = 'archive:remove-scott-warren';
    protected $description = 'Delete the Scott Warren prisoner record (never served jail time)';

    public function handle(): int {
        $p = Prisoner::where('name', 'Scott Warren')->first();
        if (! $p) {
            $this->info('No Scott Warren record found.');

            return self::SUCCESS;
        }
        $p->cases()->delete();
        $p->delete();
        $this->info('Deleted: Scott Warren.');

        return self::SUCCESS;
    }
}
