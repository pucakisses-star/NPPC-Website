<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Mufid Fawaz Alkhader's profile photo (his booking photo, cropped to a
 * portrait) from the committed image. Copies the file to the public disk and
 * sets only the photo. Matches the live record by slug, then by "Alkhader"
 * (deliberately not "Mufid", to avoid the unrelated Mufid Abdulqader record).
 * Idempotent.
 */
final class SetAlkhaderPhoto extends Command
{
    protected $signature = 'prisoners:set-alkhader-photo';

    protected $description = "Set Mufid Fawaz Alkhader's profile photo from the committed image";

    private const SOURCE = 'data/photos/legacy/mufid-fawaz-alkhader.jpg';

    private const PHOTO = 'prisoners/mufid-fawaz-alkhader.jpg';

    public function handle(): int
    {
        $source = database_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: database/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'mufid-fawaz-alkhader')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Alkhader%')->first();

        if (! $prisoner) {
            $this->warn('No Mufid Fawaz Alkhader record found — photo copied, but no record to attach it to.');

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO;
        $prisoner->save();
        $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
