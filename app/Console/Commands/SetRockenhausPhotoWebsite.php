<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Conrad Rockenhaus's profile photo (from the committed image) and his
 * website (https://rockenhaus.com). Idempotent; matches the live record by
 * slug/name (he may be absent from a local snapshot). Creates a minimal record
 * only as a fallback if none exists.
 */
final class SetRockenhausPhotoWebsite extends Command
{
    protected $signature = 'prisoners:set-rockenhaus-photo-website';

    protected $description = 'Set Conrad Rockenhaus\'s profile photo and website';

    private const SOURCE = 'images/prisoners/conrad-rockenhaus.jpg';

    private const PHOTO = 'prisoners/conrad-rockenhaus.jpg';

    private const WEBSITE = 'https://rockenhaus.com';

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
            ->where('slug', 'conrad-rockenhaus')
            ->orWhere('name', 'like', '%Rockenhaus%')
            ->first();

        if (! $prisoner) {
            $prisoner = Prisoner::create([
                'name' => 'Conrad Rockenhaus',
                'first_name' => 'Conrad',
                'last_name' => 'Rockenhaus',
                'under_review' => false,
                'photo' => self::PHOTO,
                'website' => self::WEBSITE,
            ]);
            $this->warn("No existing record found — created a minimal one: {$prisoner->name} (ID: {$prisoner->id})");

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO;
        $prisoner->website = self::WEBSITE;
        $prisoner->save();
        $this->info("Set photo and website on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
