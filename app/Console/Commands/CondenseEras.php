<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Condenses the 18th- and 19th-century decade eras into single century labels:
 * every "17x0s" era (1740s, 1770s, 1780s, 1790s, …) becomes "1700s", and every
 * "18x0s" era (1830s, 1840s, 1850s, 1860s, 1880s, 1890s, …) becomes "1800s".
 * The 1600s, 1900s, and later decade eras are left untouched.
 *
 * Idempotent — "1700s"/"1800s" map to themselves. Use --dry-run to preview.
 */
class CondenseEras extends Command
{
    protected $signature = 'prisoners:condense-eras {--dry-run : Show what would change without writing}';

    protected $description = 'Fold 1700s-century and 1800s-century decade eras into "1700s" and "1800s"';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $prisoners = Prisoner::withoutGlobalScopes()->get(['id', 'era']);

        $changed = 0;
        foreach ($prisoners as $p) {
            $era = (string) $p->era;
            $canon = $this->canonical($era);
            if ($canon === null || $canon === $era) {
                continue;
            }

            $changed++;
            $this->line(sprintf('  %-8s -> %s', $era, $canon));
            if (! $dryRun) {
                DB::table('prisoners')->where('id', $p->id)->update(['era' => $canon]);
            }
        }

        if ($dryRun) {
            $this->info("Dry run — {$changed} prisoner rows would change.");
        } else {
            Cache::forget(PrisonerApiController::cacheKey());
            $this->info("Condensed eras on {$changed} prisoner rows. API cache cleared.");
        }

        return self::SUCCESS;
    }

    private function canonical(string $era): ?string
    {
        if (preg_match('/^17\d0s$/', $era)) {
            return '1700s';
        }
        if (preg_match('/^18\d0s$/', $era)) {
            return '1800s';
        }

        return null;
    }
}
