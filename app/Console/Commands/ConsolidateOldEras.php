<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Consolidates the pre-1900 decade eras into two century eras: any 1700s-range
 * decade (1710s…1790s) becomes "1700s", and any 1800s-range decade
 * (1810s…1890s) becomes "1800s". The modern eras (1900s onward) keep their
 * decade granularity, and the lone 1600s era is left as-is.
 *
 * On the current data this folds 1770s -> 1700s and 1810s -> 1800s.
 * Query-builder writes (no model events); idempotent; busts the prisoner API
 * cache. Re-run prisoners:normalize-sort-order afterward so ordering settles.
 */
final class ConsolidateOldEras extends Command
{
    protected $signature = 'prisoners:consolidate-old-eras {--dry : Preview without writing}';

    protected $description = 'Fold 1700s-range and 1800s-range decade eras into single "1700s" and "1800s" eras';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry');
        $changes = [];
        $count = 0;

        Prisoner::withUnderReview()
            ->where(fn ($q) => $q->where('era', 'like', '17_0s')->orWhere('era', 'like', '18_0s'))
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($dry, &$changes, &$count) {
                foreach ($rows as $p) {
                    $era = (string) $p->era;
                    $target = str_starts_with($era, '17') ? '1700s' : '1800s';
                    if ($era === $target) {
                        continue; // already the century era — nothing to do
                    }

                    if (! $dry) {
                        Prisoner::withUnderReview()->whereKey($p->getKey())->update(['era' => $target]);
                    }
                    $changes["{$era} → {$target}"] = ($changes["{$era} → {$target}"] ?? 0) + 1;
                    $count++;
                }
            });

        $verb = $dry ? 'Would change' : 'Changed';
        $this->info("{$verb} {$count} record(s).");
        ksort($changes);
        foreach ($changes as $label => $n) {
            $this->line("  {$label}: {$n}");
        }

        if (! $dry && $count > 0) {
            Cache::forget(PrisonerApiController::cacheKey());
            $this->comment('Re-run prisoners:normalize-sort-order to settle the ordering.');
        }

        return self::SUCCESS;
    }
}
