<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Roy Wood's profile photo from the committed source image. Copies the
 * file to the public disk and sets only the photo. Matches the live record by
 * slug first, then name. Idempotent.
 */
final class SetRoyWoodPhoto extends Command
{
    protected $signature = 'prisoners:set-roy-wood-photo';

    protected $description = "Set Roy Wood's profile photo from the committed image";

    private const SOURCE = 'images/prisoners/roy-wood.jpg';

    private const PHOTO = 'prisoners/roy-wood.jpg';

    public function handle(): int
    {
        $source = public_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: public/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'roy-wood')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Roy%Wood%')->first();

        if (! $prisoner) {
            $this->warn('No Roy Wood record found — photo copied, but no record to attach it to.');

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO;
        $prisoner->save();
        $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
