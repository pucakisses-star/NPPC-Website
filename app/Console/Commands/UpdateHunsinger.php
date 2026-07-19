<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Richard Hunsinger's profile photo and his support/Twitter (X) link
 * (https://x.com/DefendRichard). Copies the photo to the public disk and
 * updates only those two fields. Matches the live record by slug, then name.
 * Idempotent.
 */
final class UpdateHunsinger extends Command
{
    protected $signature = 'prisoners:update-hunsinger';

    protected $description = "Set Richard Hunsinger's photo and X/Twitter link";

    private const SOURCE = 'data/photos/legacy/richard-hunsinger.jpg';

    private const PHOTO = 'prisoners/richard-hunsinger.jpg';

    private const TWITTER = 'https://x.com/DefendRichard';

    public function handle(): int
    {
        $source = database_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: database/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'richard-hunsinger')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Hunsinger%')->first();

        if (! $prisoner) {
            $this->warn('No Richard Hunsinger record found — photo copied, but no record to update.');

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO;
        $prisoner->twitter = self::TWITTER;
        $prisoner->save();
        $this->info("Set photo and X link on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
