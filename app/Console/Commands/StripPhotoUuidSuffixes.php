<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Strips Filament-style UUID suffixes from prisoner photo filenames in
 * storage/app/public/prisoners/ and updates the matching Prisoner.photo
 * column. Example:
 *   albert-woodfox-19b2e3b5-2bd8-40e1-844e-2efb195c5681.jpg
 *     -> albert-woodfox.jpg
 *
 * Operates only on filenames that actually contain the trailing
 * 8-4-4-4-12 hex UUID pattern. Bare-ULID filenames (no slug stem) and
 * already-clean filenames are left alone.
 *
 * Use --dry-run to preview the renames without touching disk or DB.
 */
final class StripPhotoUuidSuffixes extends Command {
    protected $signature = 'prisoners:strip-photo-uuid-suffixes {--dry-run : Preview without changing anything}';
    protected $description = 'Rename prisoners/*.{jpg,png,...} files to drop trailing UUID suffixes and update Prisoner.photo';

    public function handle(): int {
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');
        $uuidRe = '/^(?<stem>.+)-[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.(?<ext>jpg|jpeg|png|webp|gif)$/i';

        $files = $disk->files('prisoners');
        $renamed = 0;
        $skipped = 0;
        $collisions = 0;
        $dbUpdated = 0;

        foreach ($files as $path) {
            $name = basename($path);
            if (! preg_match($uuidRe, $name, $m)) {
                $skipped++;

                continue;
            }
            $stem = $m['stem'];
            $ext = strtolower($m['ext']);
            $target = "prisoners/{$stem}.{$ext}";

            if ($target === $path) {
                $skipped++;

                continue;
            }

            $finalTarget = $target;
            if ($disk->exists($finalTarget) && $finalTarget !== $path) {
                $n = 2;
                do {
                    $finalTarget = "prisoners/{$stem}-{$n}.{$ext}";
                    $n++;
                } while ($disk->exists($finalTarget));
                $collisions++;
                $this->warn("Collision: {$target} exists; using {$finalTarget}");
            }

            $this->line(($dryRun ? '[dry-run] ' : '')."mv {$path} -> {$finalTarget}");

            if (! $dryRun) {
                $disk->move($path, $finalTarget);
            }
            $renamed++;

            $owner = Prisoner::where('photo', $path)->first();
            if ($owner) {
                if (! $dryRun) {
                    $owner->photo = $finalTarget;
                    $owner->save();
                }
                $dbUpdated++;
                $this->line(($dryRun ? '[dry-run] ' : '')."  db: prisoner {$owner->name} photo -> {$finalTarget}");
            }
        }

        $this->info("\nDone. Renamed={$renamed} DBUpdated={$dbUpdated} Collisions={$collisions} Skipped={$skipped}".($dryRun ? ' (DRY RUN)' : ''));

        return self::SUCCESS;
    }
}
