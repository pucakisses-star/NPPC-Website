<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches profile photos for the pro-life FACE Act prisoners profiled in the
 * NC Register / CNA article "Meet the Pro-Life Prisoners Whom Trump Is Expected
 * to Pardon" who previously had no photo. Each committed image is copied to the
 * public disk and set on the matching prisoner (by slug). Idempotent.
 */
final class SetProLifePrisonerPhotos extends Command
{
    protected $signature = 'prisoners:set-prolife-photos';

    protected $description = 'Set photos for the pro-life FACE Act prisoners from the NCRegister article';

    /** prisoner slug (also the committed image filename under public/images/prisoners/) */
    private const SLUGS = [
        'bevelyn-beatty-williams',
        'eva-edl',
        'jean-marshall',
        'jonathan-darnel',
        'paula-harlow',
    ];

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

        $this->info("\nDone. Set {$set} pro-life prisoner photo(s).");

        return self::SUCCESS;
    }
}
