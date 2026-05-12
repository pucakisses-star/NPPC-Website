<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Renames orphan files in storage/app/public/prisoners/ whose basename
 * is not in slug form (contains uppercase letters, spaces, etc.) to
 * lowercase slug form, e.g.
 *   prisoners/Ezra Heywood.jpg     -> prisoners/ezra-heywood.jpg
 *   prisoners/Matthew   Lyon.jpg   -> prisoners/matthew-lyon.jpg
 *
 * Only acts on ORPHAN files (no Prisoner.photo references it). After
 * renaming, also tries to find a Prisoner whose slug matches and link
 * the renamed file on the photo column. Collisions are resolved with
 * -2, -3, ... Supports --dry-run.
 */
final class SlugifyOrphanPrisonerPhotos extends Command {
    protected $signature = 'prisoners:slugify-orphan-photos {--dry-run : Preview without changing anything} {--no-link : Do not auto-link matching prisoners after rename}';
    protected $description = 'Slugify orphan prisoner photo filenames and optionally link them to matching prisoners';

    public function handle(): int {
        $dryRun = (bool) $this->option('dry-run');
        $noLink = (bool) $this->option('no-link');
        $disk = Storage::disk('public');

        $referenced = Prisoner::whereNotNull('photo')
            ->where('photo', '!=', '')
            ->pluck('photo')
            ->map(fn ($p) => ltrim($p, '/'))
            ->all();
        $referenced = array_flip($referenced);

        $renamed = 0;
        $linked = 0;
        $alreadySlug = 0;
        $collisions = 0;

        foreach ($disk->files('prisoners') as $path) {
            if (isset($referenced[$path])) {
                continue;
            }

            $name = basename($path);
            $stem = pathinfo($name, PATHINFO_FILENAME);
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $slug = Str::slug($stem);

            if ($slug === $stem && $ext === pathinfo($name, PATHINFO_EXTENSION)) {
                $alreadySlug++;

                continue;
            }
            if ($slug === '') {
                $this->warn("Empty slug for {$path} — skipping.");

                continue;
            }

            $target = "prisoners/{$slug}.{$ext}";
            $finalTarget = $target;
            if ($disk->exists($finalTarget) && $finalTarget !== $path) {
                $n = 2;
                do {
                    $finalTarget = "prisoners/{$slug}-{$n}.{$ext}";
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

            if (! $noLink) {
                $candidate = Prisoner::where('slug', $slug)->first();
                if ($candidate && empty($candidate->photo)) {
                    $this->line(($dryRun ? '[dry-run] ' : '')."  link prisoner {$candidate->name} -> {$finalTarget}");
                    if (! $dryRun) {
                        $candidate->photo = $finalTarget;
                        $candidate->save();
                    }
                    $linked++;
                }
            }
        }

        $this->info("\nDone. Renamed={$renamed} Linked={$linked} AlreadySlug={$alreadySlug} Collisions={$collisions}".($dryRun ? ' (DRY RUN)' : ''));

        return self::SUCCESS;
    }
}
