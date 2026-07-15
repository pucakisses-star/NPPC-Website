<?php

namespace App\Console\Commands;

use App\Models\Petition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Gives every imageless petition a photograph, and backfills each
 * petition's state (which powers the Filter by State dropdown on
 * /petitions). Images ship in database/data/photos/petitions/ (named by
 * petition slug; sources and licenses in CREDITS.md there) and install to
 * the public disk. Petitions that already have an image are never touched;
 * idempotent and re-runnable.
 */
class AddPetitionImages extends Command {
    protected $signature = 'petitions:add-images';
    protected $description = 'Attach photos and state metadata to petitions';

    /** slug => state the campaign concerns ("Federal" for nationwide). */
    private const STATES = [
        'end-bop-communications-management-units' => 'Federal',
        'restore-physical-mail-prisons' => 'Federal',
        'free-oso-blanco-byron-chubbuck' => 'Federal',
        'free-leonard-peltier' => 'Federal',
        'medical-clemency-kamau-sadiki' => 'Georgia',
        'free-veronza-bowers' => 'Federal',
        'pardon-daniel-hale' => 'Federal',
        'end-espionage-act-prosecutions-of-journalists' => 'Federal',
        'pardon-julian-assange' => 'Federal',
        'full-pardon-chelsea-manning' => 'Federal',
        'pardon-steven-donziger' => 'New York',
        'free-mumia-abu-jamal' => 'Pennsylvania',
        'drop-charges-gaza-encampment-defendants' => 'New York',
        'free-marius-mason' => 'Texas',
        'free-aafia-siddiqui' => 'Texas',
        'drop-stop-cop-city-rico-charges' => 'Georgia',
        'justice-for-tortuguita' => 'Georgia',
    ];

    public function handle(): int {
        foreach (self::STATES as $slug => $state) {
            Petition::where('slug', $slug)->whereNull('state')->update(['state' => $state]);
        }
        $this->info('State metadata backfilled.');

        $done = 0;

        foreach (glob(database_path('data/photos/petitions/*.jpg')) as $src) {
            $slug = basename($src, '.jpg');
            $petition = Petition::where('slug', $slug)->first();
            if (! $petition) {
                $this->warn("No petition with slug {$slug} — skipped.");
                continue;
            }
            if ($petition->image) {
                $this->line("Already has an image: {$petition->title}");
                continue;
            }
            $disk = Storage::disk('public');
            $dest = 'petitions/'.$slug.'.jpg';
            $disk->makeDirectory('petitions');
            $disk->put($dest, file_get_contents($src));
            $petition->image = $dest;
            $petition->save();
            $this->info("Attached image to: {$petition->title}");
            $done++;
        }

        $this->info("Done. {$done} image(s) attached.");

        return self::SUCCESS;
    }
}
