<?php

namespace App\Console\Commands;

use App\Models\Partner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Deletes files in storage/app/public/partners/ that are not referenced
 * by any Partner.logo column. Use --dry-run to preview first.
 */
final class PruneUnusedPartnerLogos extends Command {
    protected $signature = 'partners:prune-unused-logos {--dry-run : List orphans without deleting}';
    protected $description = 'Delete files in storage/app/public/partners/ not referenced by any Partner.logo';

    public function handle(): int {
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');

        $referenced = Partner::whereNotNull('logo')
            ->where('logo', '!=', '')
            ->pluck('logo')
            ->map(fn ($p) => ltrim($p, '/'))
            ->all();
        $referenced = array_flip($referenced);

        $files = $disk->files('partners');
        $deleted = 0;
        $kept = 0;

        foreach ($files as $path) {
            if (isset($referenced[$path])) {
                $kept++;

                continue;
            }
            $this->line(($dryRun ? '[dry-run] ' : '')."delete {$path}");
            if (! $dryRun) {
                $disk->delete($path);
            }
            $deleted++;
        }

        $this->info("\nDone. Deleted={$deleted} Kept={$kept}".($dryRun ? ' (DRY RUN)' : ''));

        return self::SUCCESS;
    }
}
