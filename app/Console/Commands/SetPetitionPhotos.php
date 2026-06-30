<?php

namespace App\Console\Commands;

use App\Models\Petition;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Gives each petition a photo. For every petition that doesn't already have an
 * image:
 *   1. uses a committed image at public/images/petitions/<slug>.jpg if present;
 *   2. otherwise, for a petition about a documented prisoner, reuses that
 *      prisoner's existing profile photo (so the card shows the same on-brand
 *      portrait as the prisoner page).
 *
 * Idempotent and non-destructive: petitions that already have an image are left
 * untouched. Reports any petition still without a photo (e.g. thematic ones).
 */
final class SetPetitionPhotos extends Command
{
    protected $signature = 'petitions:set-photos';

    protected $description = "Give each petition a photo (committed image, else the matching prisoner's photo)";

    /** petition slug => prisoner name fragments to try (first match with a photo wins) */
    private const MAP = [
        'free-leonard-peltier' => ['Leonard Peltier'],
        'free-mumia-abu-jamal' => ['Mumia Abu-Jamal'],
        'free-veronza-bowers' => ['Veronza Bowers'],
        'medical-clemency-kamau-sadiki' => ['Kamau Sadiki'],
        'free-marius-mason' => ['Marius Mason', 'Marie Mason'],
        'free-oso-blanco-byron-chubbuck' => ['Byron Chubbuck', 'Oso Blanco'],
        'free-aafia-siddiqui' => ['Aafia Siddiqui'],
        'pardon-daniel-hale' => ['Daniel Hale'],
        'full-pardon-chelsea-manning' => ['Chelsea Manning'],
        'pardon-steven-donziger' => ['Steven Donziger'],
        'pardon-julian-assange' => ['Julian Assange'],
        'justice-for-tortuguita' => ['Tortuguita', 'Paez Terán', 'Manuel Esteban'],
        'aaron-bushnell-memorial-ceasefire' => ['Aaron Bushnell'],
    ];

    public function handle(): int
    {
        $set = 0;
        $skipped = 0;
        $missing = [];

        foreach (Petition::all() as $petition) {
            if (! empty($petition->image)) {
                $skipped++;

                continue;
            }

            // 1) Committed image override.
            $committed = public_path('images/petitions/'.$petition->slug.'.jpg');
            if (is_file($committed)) {
                $dest = 'petitions/'.$petition->slug.'.jpg';
                Storage::disk('public')->put($dest, file_get_contents($committed));
                $petition->image = $dest;
                $petition->save();
                $this->info("committed image -> {$petition->slug}");
                $set++;

                continue;
            }

            // 2) Reuse a matching prisoner's photo.
            $found = false;
            foreach (self::MAP[$petition->slug] ?? [] as $fragment) {
                $prisoner = Prisoner::withoutGlobalScopes()
                    ->where('name', 'like', '%'.$fragment.'%')
                    ->whereNotNull('photo')
                    ->where('photo', '!=', '')
                    ->first();
                if ($prisoner) {
                    $petition->image = $prisoner->photo;
                    $petition->save();
                    $this->info("prisoner photo ({$prisoner->name}) -> {$petition->slug}");
                    $set++;
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                $missing[] = $petition->slug;
            }
        }

        $this->info("\nDone. Set {$set} photo(s); {$skipped} already had one.");
        if ($missing) {
            $this->warn('Still without a photo ('.count($missing).'): '.implode(', ', $missing));
        }

        return self::SUCCESS;
    }
}
