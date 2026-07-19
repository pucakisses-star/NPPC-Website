<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Martin Leonel Perez Castro's profile photo, cropped to a
 * head-and-shoulders portrait from the supplied news image (El Espectador).
 * Copies the committed image to the public disk and sets only the photo.
 * Matches the live record by slug, then surname. Idempotent.
 */
final class SetPerezCastroPhoto extends Command
{
    protected $signature = 'prisoners:set-perez-castro-photo';

    protected $description = "Set Martin Leonel Perez Castro's profile photo from the committed image";

    private const SOURCE = 'data/photos/legacy/martin-leonel-perez-castro.jpg';

    private const PHOTO = 'prisoners/martin-leonel-perez-castro.jpg';

    public function handle(): int
    {
        $source = database_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: database/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'martin-leonel-perez-castro')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Perez Castro%')->first();

        if (! $prisoner) {
            $this->warn('No Martin Leonel Perez Castro record found — photo copied, but no record to attach it to.');

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO;
        $prisoner->save();
        $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
