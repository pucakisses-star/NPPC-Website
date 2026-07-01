<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets profile photos for Timothy Martin and Joanna Smith (the Declare
 * Emergency activists in the 2023 National Gallery of Art Degas paint protest),
 * cropped from the incident photo. Copies each committed image to the public
 * disk and attaches it to the matching prisoner by slug. Idempotent.
 */
final class SetDegasProtestPhotos extends Command
{
    protected $signature = 'prisoners:set-degas-photos';

    protected $description = 'Set photos for Timothy Martin and Joanna Smith (Degas paint protest)';

    private const SLUGS = ['timothy-martin', 'joanna-smith'];

    public function handle(): int
    {
        $set = 0;

        foreach (self::SLUGS as $slug) {
            $source = public_path("images/prisoners/{$slug}.jpg");
            if (! is_file($source)) {
                $this->warn("Source image missing: public/images/prisoners/{$slug}.jpg");

                continue;
            }

            $dest = "prisoners/{$slug}.jpg";
            Storage::disk('public')->put($dest, file_get_contents($source));

            $prisoner = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $prisoner) {
                $this->warn("No prisoner with slug '{$slug}' — image copied, not attached.");

                continue;
            }

            $prisoner->photo = $dest;
            $prisoner->save();
            $this->info("Set photo on {$prisoner->name}");
            $set++;
        }

        $this->info("\nDone. Set {$set} photo(s).");

        return self::SUCCESS;
    }
}
