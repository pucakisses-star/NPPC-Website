<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Romaine "Chip" Fitzgerald's profile photo to a version cropped only on
 * the left/right sides (15% off each side, full height preserved) of his
 * original photo. There are currently two records for him on the live site;
 * each gets its own original trimmed accordingly. Scoped by slug so the
 * unrelated "Edward J. Fitzgerald" is untouched. Idempotent.
 */
final class SetFitzgeraldPhoto extends Command
{
    protected $signature = 'prisoners:set-fitzgerald-photo';

    protected $description = 'Set Romaine "Chip" Fitzgerald\'s photo (sides trimmed 15%, full height) on his record(s)';

    /** slug => committed source image under public/ */
    private const PHOTOS = [
        'romaine-fitzgerald' => 'data/photos/legacy/romaine-fitzgerald.jpg',
        'romaine-chip-fitzgerald' => 'data/photos/legacy/romaine-chip-fitzgerald.jpg',
    ];

    public function handle(): int
    {
        $set = 0;

        foreach (self::PHOTOS as $slug => $source) {
            $sourcePath = public_path($source);
            if (! is_file($sourcePath)) {
                $this->error('Source image not found: database/'.$source);

                continue;
            }

            $dest = 'prisoners/'.basename($source);
            Storage::disk('public')->put($dest, file_get_contents($sourcePath));

            $prisoner = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $prisoner) {
                $this->warn("No prisoner with slug '{$slug}' — photo copied to {$dest} but not attached.");

                continue;
            }

            $prisoner->photo = $dest;
            $prisoner->save();
            $set++;
            $this->info("Set photo on {$prisoner->name} (/prisoner/{$prisoner->slug}).");
        }

        $this->info("\nDone. {$set} record(s) updated.");

        return self::SUCCESS;
    }
}
