<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Michael Petrelis's profile photo from the committed source image
 * (public/images/prisoners/michael-petrelis.jpg, a CC BY-SA 3.0 photo from
 * Wikimedia Commons). Copies it to the public disk and sets only the photo.
 * Matches the live record by slug, then surname. Idempotent.
 */
final class SetPetrelisPhoto extends Command
{
    protected $signature = 'prisoners:set-petrelis-photo';

    protected $description = "Set Michael Petrelis's profile photo from the committed image";

    private const SOURCE = 'images/prisoners/michael-petrelis.jpg';

    private const PHOTO = 'prisoners/michael-petrelis.jpg';

    public function handle(): int
    {
        $source = public_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: public/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'michael-petrelis')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Petrelis%')->first();

        if (! $prisoner) {
            $this->warn('No Michael Petrelis record found — photo copied, but no record to attach it to.');

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO;
        $prisoner->save();
        $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
