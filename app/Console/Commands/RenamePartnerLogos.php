<?php

namespace App\Console\Commands;

use App\Models\Partner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Renames each Partner.logo file in storage/app/public/partners/ to use
 * the partner's slugified name, e.g.
 *   partners/01KQPH52N7NHS42BFCVHPXA907.png
 *     -> partners/national-lawyers-guild.png
 * Updates Partner.logo accordingly. Handles collisions by appending
 * -2, -3, ... Supports --dry-run.
 */
final class RenamePartnerLogos extends Command {
    protected $signature = 'partners:rename-logos {--dry-run : Preview without changing anything}';
    protected $description = 'Rename partner logo files to match the partner name';

    public function handle(): int {
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');

        $renamed = 0;
        $alreadyClean = 0;
        $missing = 0;
        $collisions = 0;

        $partners = Partner::whereNotNull('logo')->where('logo', '!=', '')->get();

        foreach ($partners as $partner) {
            $current = ltrim($partner->logo, '/');
            if (! $disk->exists($current)) {
                $this->warn("Missing file for {$partner->name}: {$current}");
                $missing++;

                continue;
            }

            $ext = strtolower(pathinfo($current, PATHINFO_EXTENSION));
            $slug = Str::slug($partner->name);
            if ($slug === '') {
                $this->warn("Empty slug for partner id={$partner->id} name=\"{$partner->name}\" — skipping.");

                continue;
            }
            $target = "partners/{$slug}.{$ext}";

            if ($target === $current) {
                $alreadyClean++;

                continue;
            }

            $finalTarget = $target;
            if ($disk->exists($finalTarget)) {
                $n = 2;
                do {
                    $finalTarget = "partners/{$slug}-{$n}.{$ext}";
                    $n++;
                } while ($disk->exists($finalTarget));
                $collisions++;
                $this->warn("Collision: {$target} exists; using {$finalTarget}");
            }

            $this->line(($dryRun ? '[dry-run] ' : '')."mv {$current} -> {$finalTarget}  ({$partner->name})");

            if (! $dryRun) {
                $disk->move($current, $finalTarget);
                $partner->logo = $finalTarget;
                $partner->save();
            }
            $renamed++;
        }

        $this->info("\nDone. Renamed={$renamed} AlreadyClean={$alreadyClean} Missing={$missing} Collisions={$collisions}".($dryRun ? ' (DRY RUN)' : ''));

        return self::SUCCESS;
    }
}
