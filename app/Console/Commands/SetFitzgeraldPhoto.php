<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Romaine "Chip" Fitzgerald's profile photo to a version cropped on the
 * left/right (trimming the wide landscape down to a centered portrait) from the
 * committed image at public/images/prisoners/romaine-fitzgerald.jpg. There are
 * currently two records for him on the live site ("Romaine Fitzgerald" and
 * 'Romaine "Chip" Fitzgerald'); this sets the photo on both so whichever page is
 * viewed is fixed. Scoped to Romaine/Chip so the unrelated "Edward J. Fitzgerald"
 * is untouched. Idempotent.
 */
final class SetFitzgeraldPhoto extends Command
{
    protected $signature = 'prisoners:set-fitzgerald-photo';

    protected $description = 'Set Romaine "Chip" Fitzgerald\'s photo (cropped sides) on his record(s)';

    private const SOURCE = 'images/prisoners/romaine-fitzgerald.jpg';

    private const PHOTO = 'prisoners/romaine-fitzgerald.jpg';

    public function handle(): int
    {
        $source = public_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: public/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $prisoners = Prisoner::withoutGlobalScopes()
            ->whereIn('slug', ['romaine-fitzgerald', 'romaine-chip-fitzgerald'])
            ->orWhere('name', 'like', '%Romaine%Fitzgerald%')
            ->get();

        if ($prisoners->isEmpty()) {
            $this->warn('No Romaine "Chip" Fitzgerald record found — photo copied but not attached.');

            return self::SUCCESS;
        }

        foreach ($prisoners as $prisoner) {
            $prisoner->photo = self::PHOTO;
            $prisoner->save();
            $this->info("Set photo on {$prisoner->name} (/prisoner/{$prisoner->slug}).");
        }

        return self::SUCCESS;
    }
}
