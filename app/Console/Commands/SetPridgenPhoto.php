<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Alexander Pridgen's profile photo from the committed booking photo at
 * database/data/photos/legacy/alexander-pridgen.jpg (cropped to 3:4). Copies it to
 * the public disk and points the record at it. Idempotent; matches the live
 * record by slug/name (it may not be present in a local snapshot).
 */
final class SetPridgenPhoto extends Command
{
    protected $signature = 'prisoners:set-pridgen-photo';

    protected $description = 'Set Alexander Pridgen\'s profile photo from the committed cropped image';

    private const SOURCE = 'data/photos/legacy/alexander-pridgen.jpg';

    private const PHOTO = 'prisoners/alexander-pridgen.jpg';

    public function handle(): int
    {
        $source = database_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: database/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $prisoner = Prisoner::withoutGlobalScopes()
            ->where('slug', 'alexander-pridgen')
            ->orWhere('name', 'like', '%Pridgen%')
            ->first();

        if (! $prisoner) {
            $this->warn('Alexander Pridgen not found — photo copied but not attached.');

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO;
        $prisoner->save();
        $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
