<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Renames each Prisoner.photo file in storage/app/public/prisoners/ to
 * use the prisoner's slug, e.g.
 *   prisoners/01KNW32MJGPVCVE570S0B3X0GS.png
 *     -> prisoners/leonard-peltier.png
 * Updates Prisoner.photo accordingly. Handles collisions by appending
 * -2, -3, ... Supports --dry-run.
 */
final class RenamePrisonerPhotos extends Command {
    protected $signature = 'prisoners:rename-photos {--dry-run : Preview without changing anything} {--ulid-only : Only rename files whose current name looks like a random ULID/UUID with no recognizable slug stem}';
    protected $description = 'Rename prisoner photo files to match the prisoner slug';

    public function handle(): int {
        $dryRun = (bool) $this->option('dry-run');
        $ulidOnly = (bool) $this->option('ulid-only');
        $disk = Storage::disk('public');
        $ulidRe = '/^[0-9A-HJKMNP-TV-Z]{26}\.(jpg|jpeg|png|webp|gif)$/i';
        $uuidRe = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.(jpg|jpeg|png|webp|gif)$/i';

        $renamed = 0;
        $alreadyClean = 0;
        $missing = 0;
        $collisions = 0;
        $emptySlug = 0;

        Prisoner::whereNotNull('photo')->where('photo', '!=', '')
            ->orderBy('name')
            ->chunk(200, function ($chunk) use (&$renamed, &$alreadyClean, &$missing, &$collisions, &$emptySlug, $disk, $dryRun, $ulidOnly, $ulidRe, $uuidRe) {
                foreach ($chunk as $prisoner) {
                    $current = ltrim($prisoner->photo, '/');
                    if (! $disk->exists($current)) {
                        $this->warn("Missing file for {$prisoner->name}: {$current}");
                        $missing++;

                        continue;
                    }

                    if ($ulidOnly) {
                        $basename = basename($current);
                        if (! preg_match($ulidRe, $basename) && ! preg_match($uuidRe, $basename)) {
                            $alreadyClean++;

                            continue;
                        }
                    }

                    $slug = $prisoner->slug;
                    if (empty($slug)) {
                        $this->warn("Empty slug for prisoner id={$prisoner->id} name=\"{$prisoner->name}\" — skipping.");
                        $emptySlug++;

                        continue;
                    }

                    $ext = strtolower(pathinfo($current, PATHINFO_EXTENSION));
                    $target = "prisoners/{$slug}.{$ext}";

                    if ($target === $current) {
                        $alreadyClean++;

                        continue;
                    }

                    $finalTarget = $target;
                    if ($disk->exists($finalTarget)) {
                        $n = 2;
                        do {
                            $finalTarget = "prisoners/{$slug}-{$n}.{$ext}";
                            $n++;
                        } while ($disk->exists($finalTarget));
                        $collisions++;
                        $this->warn("Collision: {$target} exists; using {$finalTarget}");
                    }

                    $this->line(($dryRun ? '[dry-run] ' : '')."mv {$current} -> {$finalTarget}  ({$prisoner->name})");

                    if (! $dryRun) {
                        $disk->move($current, $finalTarget);
                        $prisoner->photo = $finalTarget;
                        $prisoner->save();
                    }
                    $renamed++;
                }
            });

        $this->info("\nDone. Renamed={$renamed} AlreadyClean={$alreadyClean} Missing={$missing} Collisions={$collisions} EmptySlug={$emptySlug}".($dryRun ? ' (DRY RUN)' : ''));

        return self::SUCCESS;
    }
}
