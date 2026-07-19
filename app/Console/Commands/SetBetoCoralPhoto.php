<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Beto Coral's prisoner photo. The image was downloaded from Facebook
 * (whose CDN URLs are signed and expire) and committed to
 * database/data/photos/legacy/beto-coral.jpg; this command copies it onto the public
 * disk (where prisoner photos are served from) and points the record at it.
 * Idempotent; only updates the prisoner if it exists.
 */
final class SetBetoCoralPhoto extends Command
{
    protected $signature = 'prisoners:set-beto-coral-photo';

    protected $description = 'Set Beto Coral\'s profile photo from the committed image';

    private const SOURCE = 'data/photos/legacy/beto-coral.jpg';

    private const PHOTO = 'prisoners/beto-coral.jpg';

    public function handle(): int
    {
        $source = database_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: database/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'beto-coral')->first();
        if (! $prisoner) {
            $this->warn('Beto Coral not found — run prisoners:add-beto-coral first, then re-run this.');

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO;
        $prisoner->save();
        $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
