<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets the profile photo for Daniel Sanchez Estrada — the Prairieland Defendant
 * who goes by "Des" — from the committed source image (cropped to a
 * head-and-shoulders portrait). Copies the file to the public disk and sets
 * only the photo. Matches the live record by slug, then by "Sanchez Estrada"
 * (deliberately not "Sanchez", to avoid the separate Dario Sanchez record).
 * Idempotent.
 */
final class SetDesPhoto extends Command
{
    protected $signature = 'prisoners:set-des-photo';

    protected $description = "Set Daniel Sanchez Estrada's (Des) profile photo from the committed image";

    private const SOURCE = 'data/photos/legacy/daniel-sanchez-estrada.jpg';

    private const PHOTO = 'prisoners/daniel-sanchez-estrada.jpg';

    public function handle(): int
    {
        $source = database_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: database/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'daniel-sanchez-estrada')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Sanchez Estrada%')->first();

        if (! $prisoner) {
            $this->warn('No Daniel Sanchez Estrada record found — photo copied, but no record to attach it to.');

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO;
        $prisoner->save();
        $this->info("Set photo on {$prisoner->name} (AKA {$prisoner->aka}). View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
