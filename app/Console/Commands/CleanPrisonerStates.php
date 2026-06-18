<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Clears the prisoners.state column for any value that is not an actual U.S.
 * state, the District of Columbia, or a U.S. territory — removing foreign
 * locations (e.g. Australia, United Kingdom, Cuba, South Korea) and the bare
 * "United States" placeholder from the state field and its filter list.
 *
 * Matching is case-insensitive and trimmed; a few common variants for D.C.
 * and the territories are accepted. Idempotent; supports --dry-run.
 */
class CleanPrisonerStates extends Command
{
    protected $signature = 'prisoners:clean-states {--dry-run : Show what would be cleared without writing}';

    protected $description = 'Null any prisoner state that is not a U.S. state, D.C., or territory';

    /** Canonical valid values (50 states + D.C. + U.S. territories). */
    private const VALID = [
        'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut',
        'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa',
        'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan',
        'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire',
        'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio',
        'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota',
        'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia',
        'Wisconsin', 'Wyoming',
        'District of Columbia',
        'Puerto Rico', 'Guam', 'U.S. Virgin Islands', 'American Samoa', 'Northern Mariana Islands',
    ];

    /** Accepted alternate spellings (normalized) for the above. */
    private const ALIASES = [
        'washington, d.c.', 'washington d.c.', 'washington dc', 'd.c.', 'dc',
        'virgin islands', 'us virgin islands', 'u.s. virgin islands',
        'commonwealth of puerto rico', 'guam (u.s. territory)',
    ];

    public function handle(): int
    {
        $accepted = array_flip(array_map(
            fn ($s) => strtolower(trim($s)),
            array_merge(self::VALID, self::ALIASES)
        ));

        $dry = (bool) $this->option('dry-run');
        $removed = [];
        $count = 0;

        foreach (Prisoner::withUnderReview()->whereNotNull('state')->where('state', '!=', '')->get() as $p) {
            $norm = strtolower(trim((string) $p->state));
            if (isset($accepted[$norm])) {
                continue;
            }

            $removed[$p->state] = ($removed[$p->state] ?? 0) + 1;
            $count++;

            if (! $dry) {
                $p->state = null;
                $p->save();
            }
        }

        if ($removed) {
            ksort($removed);
            $this->line(($dry ? 'Would clear' : 'Cleared').' these non-U.S. state values:');
            foreach ($removed as $val => $n) {
                $this->line(sprintf('  %-22s %d', $val, $n));
            }
        }

        $this->info("\n".($dry ? 'Would clear' : 'Cleared')." {$count} record(s) across ".count($removed).' value(s).');

        return self::SUCCESS;
    }
}
