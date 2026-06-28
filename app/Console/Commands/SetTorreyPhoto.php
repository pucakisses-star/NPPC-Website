<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Charles Turner Torrey's profile photo, cropped to the oval engraved
 * portrait from his Wikipedia article (a 19th-century abolitionist portrait,
 * with the handwritten caption removed). Copies the committed image to the
 * public disk and sets only the photo. Matches the live record by slug, then
 * surname. Idempotent.
 */
final class SetTorreyPhoto extends Command
{
    protected $signature = 'prisoners:set-torrey-photo';

    protected $description = "Set Charles Turner Torrey's profile photo from the committed image";

    private const SOURCE = 'images/prisoners/charles-t-torrey.jpg';

    private const PHOTO = 'prisoners/charles-t-torrey.jpg';

    public function handle(): int
    {
        $source = public_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: public/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'charles-t-torrey')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Torrey%')->first();

        if (! $prisoner) {
            $this->warn('No Charles Turner Torrey record found — photo copied, but no record to attach it to.');

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO;
        $prisoner->save();
        $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
