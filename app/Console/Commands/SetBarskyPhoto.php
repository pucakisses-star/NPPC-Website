<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Edward K. Barsky's profile photo from the committed source image (his
 * public-domain 1937 press portrait from Wikipedia). Copies the file to the
 * public disk and sets only the photo. Matches the live record by slug, then
 * name. Idempotent.
 */
final class SetBarskyPhoto extends Command
{
    protected $signature = 'prisoners:set-barsky-photo';

    protected $description = "Set Edward K. Barsky's profile photo from the committed image";

    private const SOURCE = 'images/prisoners/edward-k-barsky.jpg';

    private const PHOTO = 'prisoners/edward-k-barsky.jpg';

    public function handle(): int
    {
        $source = public_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: public/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'edward-k-barsky')->first()
            ?? Prisoner::withoutGlobalScopes()->where('slug', 'edward-barsky')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Barsky%')->first();

        if (! $prisoner) {
            $this->warn('No Edward K. Barsky record found — photo copied, but no record to attach it to.');

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO;
        $prisoner->save();
        $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
