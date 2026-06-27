<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Hridindu Roychowdhury's profile photo from the committed source image.
 * Copies the file to the public disk and sets only the photo. Matches the live
 * record by either slug (the prod and local snapshots use slightly different
 * slugs) and falls back to the surname. Idempotent.
 */
final class SetRoychowdhuryPhoto extends Command
{
    protected $signature = 'prisoners:set-roychowdhury-photo';

    protected $description = "Set Hridindu Roychowdhury's profile photo from the committed image";

    private const SOURCE = 'images/prisoners/hridindu-roychowdhury.jpg';

    private const PHOTO = 'prisoners/hridindu-roychowdhury.jpg';

    public function handle(): int
    {
        $source = public_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: public/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'hridindu-roychowdhury')->first()
            ?? Prisoner::withoutGlobalScopes()->where('slug', 'hridindu-sankar-roychowdhury')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Roychowdhury%')->first();

        if (! $prisoner) {
            $this->warn('No Hridindu Roychowdhury record found — photo copied, but no record to attach it to.');

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO;
        $prisoner->save();
        $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
