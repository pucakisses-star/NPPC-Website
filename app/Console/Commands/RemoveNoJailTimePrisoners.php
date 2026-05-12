<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Removes prisoners who never actually served jail time:
 *   - Charges dismissed before trial / before reporting
 *   - Severed from trial for health, never tried
 *   - Convicted but conviction reversed on appeal before they reported
 *   - Killed before being brought into custody
 *   - Acquitted at trial without any pretrial detention
 *
 * Prisoners who were held even briefly pretrial (Ettor / Giovannitti
 * acquittal cases, Panther 21, Mooney co-defendants, etc.) are kept
 * since they did serve detention time.
 */
final class RemoveNoJailTimePrisoners extends Command {
    protected $signature = 'archive:remove-no-jail-time-prisoners {--dry-run}';
    protected $description = 'Delete prisoner records for people who never actually went to jail';

    public function handle(): int {
        $dry = (bool) $this->option('dry-run');
        $names = [
            // Charges dismissed / never tried, no jail
            'Owen Lattimore',
            'William Z. Foster',
            'Marion Bachrach',
            'Daniel Ellsberg',
            'Anthony Russo',
            'Judith Coplon',

            // Convicted but conviction reversed before reporting to serve
            'Benjamin Spock',
            'William Sloane Coffin Jr.',
            'Mitchell Goodman',
            'Michael Ferber',

            // Acquitted at trial without serving
            'Marcus Raskin',

            // Killed at scene / before reporting
            'Griselio Torresola',
            'David Darst',
        ];

        $deleted = 0;
        $miss = 0;
        foreach ($names as $name) {
            $p = Prisoner::where('name', $name)->first();
            if (! $p) {
                $this->warn("Not found: {$name}");
                $miss++;

                continue;
            }
            $this->info("DELETE: {$p->name}");
            if (! $dry) {
                $p->cases()->delete();
                $p->delete();
            }
            $deleted++;
        }

        $verb = $dry ? 'would delete' : 'deleted';
        $this->info("\nDone. {$verb}={$deleted} not_found={$miss} ".($dry ? '(DRY RUN)' : ''));

        return self::SUCCESS;
    }
}
