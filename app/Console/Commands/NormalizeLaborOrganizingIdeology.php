<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Collapses case variants of the "Labor organizing" ideology (e.g. the stray
 * "Labor Organizing") into the single canonical "Labor organizing". Each
 * prisoner's ideologies array is rewritten in place and de-duplicated.
 * Idempotent; supports --dry-run.
 */
final class NormalizeLaborOrganizingIdeology extends Command
{
    protected $signature = 'prisoners:normalize-labor-organizing {--dry-run : Show planned changes without writing}';

    protected $description = 'Merge case variants of "Labor organizing" into the canonical form';

    private const CANONICAL = 'Labor organizing';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $prisoners = Prisoner::withoutGlobalScopes()
            ->where('ideologies', 'like', '%abor%rganizing%')
            ->get();

        $changed = 0;
        foreach ($prisoners as $p) {
            $ideologies = is_array($p->ideologies) ? $p->ideologies : [];

            $mapped = array_map(
                fn ($v) => strtolower(trim((string) $v)) === 'labor organizing' ? self::CANONICAL : $v,
                $ideologies,
            );
            $mapped = array_values(array_unique($mapped));

            if ($mapped === $ideologies) {
                continue;
            }

            $this->line("  {$p->name}: ".implode(', ', $ideologies).'  ->  '.implode(', ', $mapped));

            if (! $dry) {
                $p->ideologies = $mapped;
                $p->save();
            }

            $changed++;
        }

        $this->newLine();
        if ($dry) {
            $this->warn("Dry run — no changes written. {$changed} record(s) would change.");
        } else {
            $this->info("Done. Normalized {$changed} record(s) to \"".self::CANONICAL.'".');
        }

        return self::SUCCESS;
    }
}
