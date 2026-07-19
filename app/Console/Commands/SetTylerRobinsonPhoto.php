<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Tyler Robinson's profile photo from the committed source image (a
 * courtroom photo cropped to a portrait). Copies the file to the public disk
 * and sets only the photo. Matches the live record by slug, then name.
 * Idempotent.
 */
final class SetTylerRobinsonPhoto extends Command
{
    protected $signature = 'prisoners:set-tyler-robinson-photo';

    protected $description = "Set Tyler Robinson's profile photo from the committed image";

    private const SOURCE = 'data/photos/legacy/tyler-robinson.jpg';

    private const PHOTO = 'prisoners/tyler-robinson.jpg';

    public function handle(): int
    {
        $source = database_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: database/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'tyler-robinson')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Tyler Robinson%')->first();

        if (! $prisoner) {
            $this->warn('No Tyler Robinson record found — photo copied, but no record to attach it to.');

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO;
        $prisoner->save();
        $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
