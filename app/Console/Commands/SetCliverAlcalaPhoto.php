<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Clíver Alcalá Cordones's profile photo from the committed, pre-processed
 * portrait at public/images/prisoners/cliver-alcala-cordones.jpg — a news photo
 * rotated +18° so his head is upright and cropped to 3:4. Copies it onto the
 * public disk (where prisoner photos are served from) and points the record at
 * it. Idempotent; only updates the prisoner if it exists.
 */
final class SetCliverAlcalaPhoto extends Command
{
    protected $signature = 'prisoners:set-cliver-alcala-photo';

    protected $description = 'Set Clíver Alcalá Cordones\'s profile photo from the committed cropped image';

    private const SOURCE = 'images/prisoners/cliver-alcala-cordones.jpg';

    private const PHOTO = 'prisoners/cliver-alcala-cordones.jpg';

    public function handle(): int
    {
        $source = public_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: public/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        // Match robustly: the live record's surname is spelled "Cordero" (slug
        // cliver-antonio-alcala-cordero), so key on the distinctive
        // "Clíver … Alcalá" combination plus both known slugs.
        $prisoner = Prisoner::withoutGlobalScopes()
            ->where('slug', 'cliver-antonio-alcala-cordero')
            ->orWhere('slug', 'cliver-alcala-cordones')
            ->orWhere('name', 'like', '%Cl_ver%Alcal%')
            ->first();

        if (! $prisoner) {
            $this->warn('Clíver Alcalá Cordones not found — photo copied but not attached.');

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO;
        $prisoner->save();
        $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
