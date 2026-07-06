<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches individual portraits of the Fort Hood Three — Dennis Mora, David
 * Samas, and James Johnson Jr. — each cropped from the public-domain group
 * photograph on the front cover of the Fort Hood Three Defense Committee
 * pamphlet (July 1966), via Wikimedia Commons (File:Fort Hood Three.jpg).
 *
 * Only sets the photo when the entry has none. Idempotent.
 */
final class SetFortHoodThreePhotos extends Command
{
    protected $signature = 'prisoners:set-fort-hood-three-photos';

    protected $description = 'Attach public-domain portraits of the Fort Hood Three (Mora, Samas, Johnson)';

    public function handle(): int
    {
        $map = [
            'Dennis Mora' => 'dennis-mora.jpg',
            'David Samas' => 'david-samas.jpg',
            'James Johnson Jr.' => 'james-johnson-jr.jpg',
        ];

        foreach ($map as $name => $file) {
            $prisoner = Prisoner::withUnderReview()->where('name', $name)->first();
            if (! $prisoner) {
                $this->warn("Not found: {$name} — skipped.");

                continue;
            }
            if (! empty($prisoner->photo)) {
                $this->info("{$name} already has a photo; left as-is.");

                continue;
            }
            $src = database_path('data/photos/'.$file);
            if (! is_file($src)) {
                $this->warn("Missing image file: {$file} — skipped {$name}.");

                continue;
            }
            Storage::disk('public')->makeDirectory('prisoners');
            Storage::disk('public')->put('prisoners/'.$file, file_get_contents($src));
            $prisoner->photo = 'prisoners/'.$file;
            $prisoner->save();
            $this->info("Attached portrait to {$name}.");
        }

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
