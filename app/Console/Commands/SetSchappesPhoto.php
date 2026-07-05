<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Morris U. Schappes's profile photo from the committed source image (his
 * public-domain March 1941 courtroom portrait, published by The Worker and held
 * by the NYU Tamiment Library, via Wikimedia Commons). Copies the file to the
 * public disk and sets only the photo. Matches the live record by slug, then
 * name. Idempotent.
 */
final class SetSchappesPhoto extends Command
{
    protected $signature = 'prisoners:set-schappes-photo';

    protected $description = "Set Morris U. Schappes's profile photo from the committed image";

    private const SOURCE = 'images/prisoners/morris-u-schappes.jpg';

    private const PHOTO = 'prisoners/morris-u-schappes.jpg';

    public function handle(): int
    {
        $source = public_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: public/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'morris-u-schappes')->first()
            ?? Prisoner::withoutGlobalScopes()->where('slug', 'morris-schappes')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Schappes%')->first();

        if (! $prisoner) {
            $this->warn('No Morris U. Schappes record found — photo copied, but no record to attach it to.');

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO;
        $prisoner->save();
        $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
