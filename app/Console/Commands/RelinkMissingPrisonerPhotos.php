<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * For prisoners whose photo column references a file that no longer
 * exists on disk, attempt to relink to a surviving file. Tries:
 *
 *   1. The clean stem (strip trailing UUID): prisoners/{stem}.{ext}
 *   2. Numeric collision variants: prisoners/{stem}-2.{ext}, -3, ...
 *   3. prisoners/{slug}.{ext} as a final fallback
 *
 * Use --dry-run to preview without writing DB.
 */
final class RelinkMissingPrisonerPhotos extends Command {
    protected $signature = 'prisoners:relink-missing-photos {--dry-run : Preview without saving}';
    protected $description = 'Restore Prisoner.photo to a surviving file when the current path 404s';

    public function handle(): int {
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');
        $uuidRe = '/^(?<stem>.+)-[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.(?<ext>jpg|jpeg|png|webp|gif)$/i';

        $relinked = 0;
        $stillBroken = 0;

        Prisoner::whereNotNull('photo')->where('photo', '!=', '')
            ->orderBy('name')
            ->chunk(200, function ($chunk) use (&$relinked, &$stillBroken, $disk, $uuidRe, $dryRun) {
                foreach ($chunk as $prisoner) {
                    $path = ltrim($prisoner->photo, '/');
                    if ($disk->exists($path)) {
                        continue;
                    }

                    $candidates = [];
                    $name = basename($path);
                    if (preg_match($uuidRe, $name, $m)) {
                        $stem = $m['stem'];
                        $ext = strtolower($m['ext']);
                        $candidates[] = "prisoners/{$stem}.{$ext}";
                        for ($n = 2; $n <= 9; $n++) {
                            $candidates[] = "prisoners/{$stem}-{$n}.{$ext}";
                        }
                    }
                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg');
                    foreach (['jpg', 'jpeg', 'png', 'webp', 'gif'] as $tryExt) {
                        $candidates[] = "prisoners/{$prisoner->slug}.{$tryExt}";
                    }

                    $found = null;
                    foreach ($candidates as $c) {
                        if ($disk->exists($c)) {
                            $found = $c;
                            break;
                        }
                    }

                    if ($found) {
                        $this->line(($dryRun ? '[dry-run] ' : '')."relink {$prisoner->name}: {$prisoner->photo} -> {$found}");
                        if (! $dryRun) {
                            $prisoner->photo = $found;
                            $prisoner->save();
                        }
                        $relinked++;
                    } else {
                        $this->warn("STILL BROKEN: {$prisoner->name}  photo={$prisoner->photo}");
                        $stillBroken++;
                    }
                }
            });

        $this->info("\nDone. Relinked={$relinked} StillBroken={$stillBroken}".($dryRun ? ' (DRY RUN)' : ''));

        return self::SUCCESS;
    }
}
