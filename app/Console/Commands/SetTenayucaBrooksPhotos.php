<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches portraits for Emma Tenayuca and her husband Homer Brooks.
 *
 *   - Emma Tenayuca: her 1930s portrait from Wikipedia (San Antonio Light /
 *     UTSA), which is non-free — filed in photos/nonfree/ and credited in
 *     CREDITS-nonfree.md.
 *   - Homer Brooks: cropped from the couple's CC BY-SA 4.0 wedding photo on
 *     Wikimedia Commons (Emma cropped out) — a free image in photos/, credited
 *     in CREDITS-homer-brooks.md.
 *
 * Only sets a photo when the prisoner currently has none, so it never overwrites
 * an existing image and is safe to re-run.
 */
class SetTenayucaBrooksPhotos extends Command
{
    protected $signature = 'prisoners:set-tenayuca-brooks-photos';

    protected $description = 'Attach photos for Emma Tenayuca (non-free portrait) and Homer Brooks (cropped from the CC BY-SA wedding photo)';

    /** Prisoner name => source path under database/data/photos. */
    private const MAP = [
        'Emma Tenayuca' => 'nonfree/emma-tenayuca.jpg',
        'Homer Brooks' => 'homer-brooks.jpg',
    ];

    public function handle(): int
    {
        Storage::disk('public')->makeDirectory('prisoners');

        $linked = 0;
        foreach (self::MAP as $name => $relSrc) {
            $src = database_path('data/photos/'.$relSrc);
            if (! is_file($src)) {
                $this->warn("Source image not found: {$src}");

                continue;
            }

            $prisoner = Prisoner::withUnderReview()->where('name', $name)->first();
            if (! $prisoner) {
                $this->warn("Prisoner not found: {$name}");

                continue;
            }

            if (! empty($prisoner->photo)) {
                $this->line("{$name} already has a photo — leaving alone.");

                continue;
            }

            $file = basename($relSrc);
            $relative = 'prisoners/'.$file;
            Storage::disk('public')->put($relative, file_get_contents($src));
            $prisoner->photo = $relative;
            $prisoner->save();
            $this->info("Linked photo for {$name}.");
            $linked++;
        }

        if ($linked > 0) {
            Cache::forget(PrisonerApiController::cacheKey());
        }

        $this->info("Done. Linked={$linked}.");

        return self::SUCCESS;
    }
}
