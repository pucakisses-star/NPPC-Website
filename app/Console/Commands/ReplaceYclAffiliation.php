<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * The "Young Communist League" (the CPUSA youth wing) is folded into its
 * parent organization for affiliation purposes: every prisoner carrying a
 * "Young Communist League" affiliation has it replaced with the canonical
 * "Communist Party USA". Each affiliation array is rewritten in place and
 * de-duplicated (so someone already tagged both ends up with a single
 * "Communist Party USA"). Idempotent — re-runs change nothing.
 */
final class ReplaceYclAffiliation extends Command
{
    protected $signature = 'prisoners:replace-ycl-affiliation {--dry-run : Show planned changes without writing}';

    protected $description = 'Replace the "Young Communist League" affiliation with "Communist Party USA"';

    private const FROM = 'young communist league';

    private const CANONICAL = 'Communist Party USA';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        // Narrow with a JSON LIKE, then match precisely in PHP so this works
        // on both MySQL and SQLite.
        $prisoners = Prisoner::withoutGlobalScopes()
            ->where('affiliation', 'like', '%Young Communist League%')
            ->get();

        $changed = 0;
        foreach ($prisoners as $p) {
            $aff = is_array($p->affiliation) ? $p->affiliation : [];

            $mapped = array_map(
                fn ($v) => strtolower(trim((string) $v)) === self::FROM ? self::CANONICAL : $v,
                $aff,
            );
            $mapped = array_values(array_unique($mapped));

            if ($mapped === $aff) {
                continue;
            }

            $this->line("  {$p->name}: ".implode(', ', $aff).'  ->  '.implode(', ', $mapped));

            if (! $dry) {
                $p->affiliation = $mapped;
                $p->save();
            }

            $changed++;
        }

        $this->newLine();
        if ($dry) {
            $this->warn("Dry run — no changes written. {$changed} record(s) would change.");
        } else {
            $this->info("Done. Updated {$changed} record(s).");
        }

        return self::SUCCESS;
    }
}
