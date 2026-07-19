<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Susan Saxe's profile photo from the committed source image. Copies it to
 * the public disk and attaches it to her record. Idempotent; matches by slug
 * then name.
 */
final class SetSaxePhoto extends Command
{
    protected $signature = 'prisoners:set-saxe-photo';

    protected $description = "Set Susan Saxe's profile photo from the committed image";

    private const SOURCE = 'data/photos/legacy/susan-saxe.jpg';

    private const PHOTO = 'prisoners/susan-saxe.jpg';

    public function handle(): int
    {
        $source = database_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: database/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));

        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'susan-saxe')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'Susan Saxe')->first();

        if (! $prisoner) {
            $this->warn('No Susan Saxe record found — photo copied, but nothing to attach it to.');

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO;
        $prisoner->save();
        $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
