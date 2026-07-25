<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Merges the "Early Republic", "Colonial America" and "American Revolution"
 * era values into a single "1700s" era. This removes those three options from
 * the database era filter and replaces them with one. Idempotent; supports
 * --dry-run.
 */
final class ConsolidateEarlyEras extends Command
{
    protected $signature = 'prisoners:consolidate-early-eras {--dry-run : Preview without writing}';

    protected $description = 'Merge Early Republic / Colonial America / American Revolution eras into "1700s"';

    private const FROM = ['early republic', 'colonial america', 'american revolution'];

    private const TO = '1700s';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $prisoners = Prisoner::withoutGlobalScopes()
            ->whereNotNull('era')
            ->get()
            ->filter(fn ($p) => in_array(strtolower(trim((string) $p->era)), self::FROM, true))
            ->values();

        if ($prisoners->isEmpty()) {
            $this->info('No prisoners in those eras — nothing to do.');

            return self::SUCCESS;
        }

        foreach ($prisoners as $p) {
            $this->line('  '.str_pad($p->name, 28)." [{$p->era}] -> ".self::TO);
            if (! $dry) {
                $p->era = self::TO;
                $p->save();
            }
        }

        \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());

        $this->newLine();
        if ($dry) {
            $this->warn('Dry run — no changes written. Would set '.$prisoners->count().' prisoner(s) to "'.self::TO.'".');
        } else {
            $this->info('Done. Set '.$prisoners->count().' prisoner(s) to "'.self::TO.'". The three era options are now merged into "'.self::TO.'".');
        }

        return self::SUCCESS;
    }
}
