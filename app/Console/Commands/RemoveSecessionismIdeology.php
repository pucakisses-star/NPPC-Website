<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Removes "Secessionism" from prisoners' ideologies. The value is dropped from
 * each ideologies array (de-duplicated); no replacement is added. Idempotent;
 * supports --dry-run.
 */
final class RemoveSecessionismIdeology extends Command
{
    protected $signature = 'prisoners:remove-secessionism-ideology {--dry-run : Show planned changes without writing}';

    protected $description = 'Remove the "Secessionism" ideology from all prisoners';

    private const TARGET = 'secessionism';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $prisoners = Prisoner::withoutGlobalScopes()
            ->where('ideologies', 'like', '%ecessionism%')
            ->get();

        $changed = 0;
        foreach ($prisoners as $p) {
            $ideologies = is_array($p->ideologies) ? $p->ideologies : [];

            $kept = array_values(array_filter(
                $ideologies,
                fn ($v) => strtolower(trim((string) $v)) !== self::TARGET,
            ));

            if ($kept === $ideologies) {
                continue;
            }

            $this->line("  {$p->name}: ".implode(', ', $ideologies).'  ->  '.(implode(', ', $kept) ?: '(none)'));

            if (! $dry) {
                $p->ideologies = $kept;
                $p->save();
            }

            $changed++;
        }

        $this->newLine();
        if ($dry) {
            $this->warn("Dry run — no changes written. {$changed} record(s) would change.");
        } else {
            $this->info("Done. Removed \"Secessionism\" from {$changed} record(s).");
        }

        return self::SUCCESS;
    }
}
