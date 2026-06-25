<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets David Webb's prisoner photo from the committed, pre-cropped portrait at
 * public/images/prisoners/david-webb.jpg (a 3:4 crop of the original news photo),
 * copying it onto the public disk where prisoner photos are served from.
 * Idempotent; only updates the prisoner if it exists.
 */
final class SetDavidWebbPhoto extends Command
{
    protected $signature = 'prisoners:set-david-webb-photo';

    protected $description = 'Set David Webb\'s profile photo from the committed cropped image';

    private const SOURCE = 'images/prisoners/david-webb.jpg';

    private const PHOTO = 'prisoners/david-webb.jpg';

    public function handle(): int
    {
        $source = public_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: public/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'david-webb')->first();
        if (! $prisoner) {
            $this->warn('David Webb not found — add the prisoner first, then re-run this.');

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO;
        $prisoner->save();
        $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
