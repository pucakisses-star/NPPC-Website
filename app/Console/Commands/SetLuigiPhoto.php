<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Luigi Mangione's profile photo (a court photo cropped to 3:4) from the
 * committed image. Matches the live record by slug/name and sets only the photo
 * (his bio etc. are left untouched); creates a minimal record only as a fallback
 * if none exists. Idempotent.
 */
final class SetLuigiPhoto extends Command
{
    protected $signature = 'prisoners:set-luigi-photo';

    protected $description = 'Set Luigi Mangione\'s profile photo from the committed cropped image';

    private const SOURCE = 'images/prisoners/luigi-mangione.jpg';

    private const PHOTO = 'prisoners/luigi-mangione.jpg';

    public function handle(): int
    {
        $source = public_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: public/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $prisoner = Prisoner::withoutGlobalScopes()
            ->where('slug', 'luigi-mangione')
            ->orWhere('name', 'like', '%Mangione%')
            ->first();

        if (! $prisoner) {
            $prisoner = Prisoner::create([
                'name' => 'Luigi Mangione', 'first_name' => 'Luigi', 'middle_name' => 'Nicholas', 'last_name' => 'Mangione',
                'gender' => 'Male', 'in_custody' => true, 'awaiting_trial' => true, 'released' => false, 'under_review' => false,
                'photo' => self::PHOTO,
                'description' => 'Luigi Nicholas Mangione is charged in the December 2024 shooting death of UnitedHealthcare CEO Brian Thompson in Manhattan. He is held awaiting trial; his case has drawn significant public attention.',
            ]);
            $this->warn("No existing record found — created a minimal one: {$prisoner->name} (ID: {$prisoner->id})");

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO;
        $prisoner->save();
        $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
