<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Normalizes the prisoner.gender column so the three variants of
 * non-binary that have accumulated (Nonbinary / Non-binary / Other)
 * all collapse to a single canonical value "Nonbinary".
 *
 * Idempotent — re-runs are no-ops.
 */
final class NormalizePrisonerGenders extends Command {
    protected $signature = 'prisoners:normalize-genders';
    protected $description = 'Collapse Nonbinary / Non-binary / Other into a single "Nonbinary" value';

    public function handle(): int {
        $canonical = 'Nonbinary';
        $aliases = ['Non-binary', 'non-binary', 'NonBinary', 'Other', 'other'];

        $updated = Prisoner::query()
            ->whereIn('gender', $aliases)
            ->update(['gender' => $canonical]);

        $this->info("Normalized {$updated} prisoner row(s) to '{$canonical}'.");
        return self::SUCCESS;
    }
}
