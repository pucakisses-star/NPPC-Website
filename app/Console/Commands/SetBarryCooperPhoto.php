<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Barry Cooper's profile photo from the committed source image. His record
 * previously pointed at prisoners/barry-cooper.webp, but that file was missing
 * on disk (404), so this points him at a committed .jpg that actually exists.
 * Copies the file to the public disk and sets only the photo. Matches the live
 * record by slug, then surname. Idempotent.
 */
final class SetBarryCooperPhoto extends Command
{
    protected $signature = 'prisoners:set-barry-cooper-photo';

    protected $description = "Set Barry Cooper's profile photo from the committed image (fixes the missing .webp)";

    private const SOURCE = 'data/photos/legacy/barry-cooper.jpg';

    private const PHOTO = 'prisoners/barry-cooper.jpg';

    public function handle(): int
    {
        $source = database_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: database/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'barry-cooper')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Barry%Cooper%')->first();

        if (! $prisoner) {
            $this->warn('No Barry Cooper record found — photo copied, but no record to attach it to.');

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO;
        $prisoner->save();
        $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
