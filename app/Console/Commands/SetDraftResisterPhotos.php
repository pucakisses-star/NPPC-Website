<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets profile photos for the 1980s Selective Service draft-registration
 * resisters, from committed, individually source-verified images:
 *   - Paul Jacob (Wikimedia Commons)
 *   - Benjamin Sasway (comdsd.org, captioned 1982 photo)
 *   - Edward Hasbrouck (Mission Local, 2025, captioned portrait)
 *   - Andy Mager (his own Flickr — 1985 trial photo)
 *   - Mark Schmucker (Gospel Herald, 1982, captioned headshot)
 *   - Gillam Kerley (Albuquerque Journal, 2025, captioned)
 *   - Gary Eklund (Des Moines Register, 1982, captioned headshot)
 * Copies each to the public disk and sets only the photo, matching by slug
 * then name. Russ Ford and Dan Rutt are intentionally absent — no correctly
 * identified photo of either could be found. Idempotent.
 */
final class SetDraftResisterPhotos extends Command
{
    protected $signature = 'prisoners:set-draft-resister-photos';

    protected $description = 'Set profile photos for the 1980s draft-registration resisters';

    public function handle(): int
    {
        // [primary slug, name fragment, image filename]
        $people = [
            ['paul-jacob', 'Paul Jacob', 'paul-jacob.jpg'],
            ['benjamin-sasway', 'Sasway', 'benjamin-sasway.jpg'],
            ['edward-hasbrouck', 'Hasbrouck', 'edward-hasbrouck.jpg'],
            ['andy-mager', 'Mager', 'andy-mager.jpg'],
            ['mark-alden-schmucker', 'Schmucker', 'mark-alden-schmucker.jpg'],
            ['gillam-kerley', 'Kerley', 'gillam-kerley.jpg'],
            ['gary-john-eklund', 'Eklund', 'gary-john-eklund.jpg'],
        ];

        $set = 0;
        foreach ($people as [$slug, $nameFragment, $file]) {
            $source = database_path('data/photos/legacy/'.$file);
            if (! is_file($source)) {
                $this->error("Source image not found: database/data/photos/legacy/{$file}");

                continue;
            }

            $photo = 'prisoners/'.$file;
            Storage::disk('public')->put($photo, file_get_contents($source));

            $p = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first()
                ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%'.$nameFragment.'%')->first();

            if (! $p) {
                $this->warn("No record found for {$nameFragment} — photo copied, not attached.");

                continue;
            }

            $p->photo = $photo;
            $p->save();
            $set++;
            $this->info("Set photo on {$p->name} (/prisoner/{$p->slug}).");
        }

        $this->info("\nDone. Set {$set} photo(s).");

        return self::SUCCESS;
    }
}
