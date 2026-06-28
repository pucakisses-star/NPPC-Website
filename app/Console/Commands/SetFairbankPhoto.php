<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Calvin Fairbank's profile photo, cropped to the engraved bust portrait
 * from his Wikipedia article (a 19th-century abolitionist book-plate engraving,
 * with the white margins and engraver's mark removed). Copies the committed
 * image to the public disk and sets only the photo. Matches the live record by
 * slug, then surname. Idempotent.
 */
final class SetFairbankPhoto extends Command
{
    protected $signature = 'prisoners:set-fairbank-photo';

    protected $description = "Set Calvin Fairbank's profile photo from the committed image";

    private const SOURCE = 'images/prisoners/calvin-fairbank.jpg';

    private const PHOTO = 'prisoners/calvin-fairbank.jpg';

    public function handle(): int
    {
        $source = public_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: public/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'calvin-fairbank')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Fairbank%')->first();

        if (! $prisoner) {
            $this->warn('No Calvin Fairbank record found — photo copied, but no record to attach it to.');

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO;
        $prisoner->save();
        $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
