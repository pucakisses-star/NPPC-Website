<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Christian Frazee's profile photo, cropped to a head-and-shoulders
 * portrait from his DuPage County booking photo (via FOX 32 Chicago). Copies
 * the committed image to the public disk and sets only the photo. Matches the
 * live record by slug, then surname. Idempotent.
 */
final class SetFrazeePhoto extends Command
{
    protected $signature = 'prisoners:set-frazee-photo';

    protected $description = "Set Christian Frazee's profile photo from the committed image";

    private const SOURCE = 'data/photos/legacy/christian-frazee.jpg';

    private const PHOTO = 'prisoners/christian-frazee.jpg';

    public function handle(): int
    {
        $source = database_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: database/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'christian-frazee')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Frazee%')->first();

        if (! $prisoner) {
            $this->warn('No Christian Frazee record found — photo copied, but no record to attach it to.');

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO;
        $prisoner->save();
        $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
