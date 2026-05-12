<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Lists files in storage/app/public/prisoners/ that are not referenced
 * by any Prisoner.photo column. Read-only by default; pass --delete
 * to actually remove them.
 */
final class ListOrphanPrisonerPhotos extends Command {
    protected $signature = 'prisoners:list-orphan-photos {--delete : Delete the orphan files after listing} {--full : Print every orphan, not just the first 50}';
    protected $description = 'Count and list prisoner photo files not referenced by any Prisoner.photo';

    public function handle(): int {
        $disk = Storage::disk('public');

        $referenced = Prisoner::whereNotNull('photo')
            ->where('photo', '!=', '')
            ->pluck('photo')
            ->map(fn ($p) => ltrim($p, '/'))
            ->all();
        $referenced = array_flip($referenced);

        $files = $disk->files('prisoners');
        $orphans = [];
        foreach ($files as $path) {
            if (! isset($referenced[$path])) {
                $orphans[] = $path;
            }
        }

        $this->info('Total files in prisoners/: '.count($files));
        $this->info('Referenced by a Prisoner record: '.(count($files) - count($orphans)));
        $this->info('Orphans: '.count($orphans));

        $show = $this->option('full') ? $orphans : array_slice($orphans, 0, 50);
        foreach ($show as $path) {
            $size = $disk->size($path);
            $this->line('  - '.$path.'  ('.number_format($size / 1024, 1).' KB)');
        }
        if (! $this->option('full') && count($orphans) > 50) {
            $this->line('  ... '.(count($orphans) - 50).' more (re-run with --full to see all)');
        }

        if ($this->option('delete')) {
            $this->warn("\nDeleting ".count($orphans).' orphan files...');
            foreach ($orphans as $path) {
                $disk->delete($path);
            }
            $this->info('Done.');
        }

        return self::SUCCESS;
    }
}
