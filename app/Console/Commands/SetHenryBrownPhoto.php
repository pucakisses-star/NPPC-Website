<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Attaches the archival portrait of Henry "Sha Sha" Brown — the Black
 * Liberation Army member briefly freed from the Manhattan House of Detention
 * ("the Tombs") in 1976 — to his existing prisoner record. The image is a
 * copyrighted Kansas City Star photo, downscaled and committed under a
 * fair-use / memorial rationale in photos/nonfree/ (see CREDITS-nonfree.md).
 *
 * Matched by name + the "Sha Sha" alias so it can't hit another Henry Brown.
 * Idempotent / re-runnable.
 */
class SetHenryBrownPhoto extends Command
{
    protected $signature = 'prisoners:set-henry-brown-photo';

    protected $description = 'Attach the archival portrait of Henry "Sha Sha" Brown (Black Liberation Army)';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()
            ->where('name', 'Henry Brown')
            ->where('aka', 'like', '%Sha Sha%')
            ->first();

        if (! $prisoner) {
            $this->warn('Henry "Sha Sha" Brown not found — nothing to update.');

            return self::FAILURE;
        }

        $src = database_path('data/photos/nonfree/brown-henry-sha-sha.jpg');
        if (! is_file($src)) {
            $this->warn('Photo source not found: '.$src);

            return self::FAILURE;
        }

        $path = 'prisoners/'.Str::slug($prisoner->name).'.jpg';
        Storage::disk('public')->makeDirectory('prisoners');
        Storage::disk('public')->put($path, (string) file_get_contents($src));
        $prisoner->photo = $path;
        $prisoner->save();

        $this->info("Set photo for {$prisoner->name} ({$prisoner->aka}) -> {$path}");

        return self::SUCCESS;
    }
}
