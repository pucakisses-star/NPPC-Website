<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Updates Hugo Carvajal ("El Pollo"): sets his photo (from his Wikipedia
 * portrait), shortens his displayed name to "Hugo Carvajal", and clears the
 * "El Pollo" AKA. His slug/URL is left unchanged (slugs only generate on
 * create). Matches the live record by slug, then name, then the old AKA.
 * Idempotent; only the photo, name fields, and aka are touched.
 */
final class UpdateHugoCarvajal extends Command
{
    protected $signature = 'prisoners:update-hugo-carvajal';

    protected $description = 'Set Hugo Carvajal\'s photo, shorten his name, and clear the El Pollo AKA';

    private const SOURCE = 'data/photos/legacy/hugo-carvajal.jpg';

    private const PHOTO = 'prisoners/hugo-carvajal.jpg';

    public function handle(): int
    {
        $source = database_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: database/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $p = Prisoner::withoutGlobalScopes()->where('slug', 'hugo-carvajal')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Carvajal%')->first()
            ?? Prisoner::withoutGlobalScopes()->where('aka', 'like', '%Pollo%')->first();

        if (! $p) {
            $this->warn('No Hugo Carvajal record found — photo copied, but no record to update.');

            return self::SUCCESS;
        }

        $p->name = 'Hugo Carvajal';
        $p->first_name = 'Hugo';
        $p->last_name = 'Carvajal';
        $p->aka = null;
        $p->photo = self::PHOTO;
        $p->save();

        $this->info("Updated {$p->name} (slug {$p->slug}): set photo, shortened name, cleared AKA.");
        $this->info("View: /prisoner/{$p->slug}");

        return self::SUCCESS;
    }
}
