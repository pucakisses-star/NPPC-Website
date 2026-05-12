<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Reports prisoners whose photo column references a file that no longer
 * exists on disk. Useful after bulk renames / prunes to see what was
 * lost.
 */
final class AuditMissingPrisonerPhotos extends Command {
    protected $signature = 'prisoners:audit-missing-photos {--full : Print every missing entry, not just the first 50}';
    protected $description = 'List prisoners whose photo file is missing on disk';

    public function handle(): int {
        $disk = Storage::disk('public');
        $missing = [];
        $checked = 0;

        Prisoner::whereNotNull('photo')->where('photo', '!=', '')
            ->orderBy('name')
            ->chunk(200, function ($chunk) use (&$missing, &$checked, $disk) {
                foreach ($chunk as $prisoner) {
                    $checked++;
                    $path = ltrim($prisoner->photo, '/');
                    if (! $disk->exists($path)) {
                        $missing[] = [
                            'name' => $prisoner->name,
                            'slug' => $prisoner->slug,
                            'photo' => $prisoner->photo,
                        ];
                    }
                }
            });

        $this->info("Checked {$checked} prisoners with a photo field set.");
        $this->info('Missing: '.count($missing));

        $show = $this->option('full') ? $missing : array_slice($missing, 0, 50);
        foreach ($show as $row) {
            $this->line("  - {$row['name']}  ({$row['slug']})  photo={$row['photo']}");
        }
        if (! $this->option('full') && count($missing) > 50) {
            $this->line('  ... '.(count($missing) - 50).' more (re-run with --full to see all)');
        }

        return self::SUCCESS;
    }
}
