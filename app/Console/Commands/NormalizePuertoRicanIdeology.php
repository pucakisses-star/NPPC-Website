<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Consolidates the near-duplicate Puerto Rican ideology labels that had drifted
 * apart in the prisoner "ideologies" lists — "Puerto Rican independence"
 * (lower-case) and "Puerto Rican nationalism" — into the single canonical label
 * "Puerto Rican Independence", de-duplicating any prisoner that ended up with
 * both. Other Puerto Rican values (e.g. the "Puerto Rican Nationalist Party"
 * affiliation, or the "Puerto Rican independence movement" affiliation) are left
 * alone — only the ideologies field is touched.
 *
 * Matching is case-insensitive on the two source phrases, so it also catches any
 * casing variant present only on production. It never rewrites anything else and
 * is idempotent: a second run finds nothing left to change.
 */
final class NormalizePuertoRicanIdeology extends Command
{
    protected $signature = 'prisoners:normalize-pr-ideology {--dry-run : Show what would change without writing}';

    protected $description = 'Merge Puerto Rican independence/nationalism ideology variants into "Puerto Rican Independence"';

    private const CANONICAL = 'Puerto Rican Independence';

    /** Lower-cased ideology values that should collapse into CANONICAL. */
    private const ALIASES = [
        'puerto rican independence',
        'puerto rican nationalism',
    ];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $changed = 0;

        foreach (Prisoner::withUnderReview()->whereNotNull('ideologies')->cursor() as $prisoner) {
            $original = array_values((array) $prisoner->ideologies);
            $touched = false;
            $mapped = [];

            foreach ($original as $value) {
                if (in_array(mb_strtolower(trim((string) $value)), self::ALIASES, true)) {
                    $mapped[] = self::CANONICAL;
                    if ($value !== self::CANONICAL) {
                        $touched = true;
                    }
                } else {
                    $mapped[] = $value;
                }
            }

            if (! $touched) {
                continue;
            }

            // Collapse the duplicate canonical entries the merge may have created.
            $deduped = array_values(array_unique($mapped));

            if (! $dry) {
                $prisoner->ideologies = $deduped;
                $prisoner->save();
            }

            $this->line("  {$prisoner->name}: [".implode(', ', $original).'] → ['.implode(', ', $deduped).']');
            $changed++;
        }

        $verb = $dry ? 'Would update' : 'Updated';
        $this->info("\n{$verb} {$changed} prisoner(s).");

        return self::SUCCESS;
    }
}
