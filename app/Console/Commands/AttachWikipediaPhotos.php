<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Attaches the freely-licensed Wikimedia Commons portraits imported for the
 * recent batches of activists (database/data/photos/*). Only public-domain and
 * Creative Commons images are included here. Copyrighted en.wikipedia uploads are
 * not free: Blanca Canales's only Wikipedia image remains excluded, while Juan
 * Antonio Corretjer's is attached separately, at the site owner's direction,
 * by prisoners:attach-nonfree-photos. Attribution for the CC images is recorded
 * in database/data/photos/CREDITS-wikipedia.md.
 *
 * The photo is copied onto the public disk and set on the prisoner each run, so
 * this is idempotent / re-syncable. Prisoners not present (e.g. on a stale local
 * snapshot) are skipped with a warning.
 */
final class AttachWikipediaPhotos extends Command
{
    protected $signature = 'prisoners:attach-wikipedia-photos';

    protected $description = 'Attach the imported freely-licensed Wikimedia photos to recent-batch prisoners';

    /** Prisoner name => committed photo file (relative to database/data/). */
    private array $map = [
        'Laura Cornelius Kellogg' => 'photos/laura-cornelius-kellogg.png',
        'Tillie Paul' => 'photos/tillie-paul.jpg',
        'Bernie Whitebear' => 'photos/bernie-whitebear.jpg',
        'Greg Grey Cloud' => 'photos/greg-grey-cloud.jpg',
        'Ramona Bennett' => 'photos/ramona-bennett.jpg',
        'Clinton Rickard' => 'photos/clinton-rickard.jpg',
        'Deskaheh' => 'photos/deskaheh.jpg',
        'Dallas Goldtooth' => 'photos/dallas-goldtooth.jpg',
        'Grace Thorpe' => 'photos/grace-thorpe.jpg',
        'Pun Plamondon' => 'photos/pun-plamondon.jpg',
        'Winona LaDuke' => 'photos/winona-laduke.jpg',
        'Clyde Bellecourt' => 'photos/clyde-bellecourt.jpg',
        'Richard Ray Whitman' => 'photos/richard-ray-whitman.jpg',
        'Wes Studi' => 'photos/wes-studi.jpg',
        'Clemente Soto Vélez' => 'photos/clemente-soto-velez.jpg',
        'Lyda Conley' => 'photos/lyda-conley.jpg',
        'Voltairine de Cleyre' => 'photos/voltairine-de-cleyre.jpg',
        'Herman Suhr' => 'photos/herman-suhr.jpg',
        'Richard "Blackie" Ford' => 'photos/richard-blackie-ford.jpg',
        'A. S. Embree' => 'photos/a-s-embree.png',
        'Leo Laukki' => 'photos/leo-laukki.jpg',
    ];

    public function handle(): int
    {
        $set = 0;
        $missing = 0;

        foreach ($this->map as $name => $relative) {
            $prisoner = Prisoner::withUnderReview()->where('name', $name)->first();
            if (! $prisoner) {
                $this->warn("Prisoner not found, skipping: {$name}");
                $missing++;

                continue;
            }

            if ($this->attachLocalPhoto($prisoner, $relative)) {
                $set++;
            } else {
                $missing++;
            }
        }

        $this->info("\nDone. Photos set={$set}  Skipped={$missing}");

        return self::SUCCESS;
    }

    private function attachLocalPhoto(Prisoner $prisoner, string $relative): bool
    {
        $src = database_path('data/'.$relative);
        if (! is_file($src)) {
            $this->warn("  Local photo not found: {$relative}");

            return false;
        }

        $ext = strtolower(pathinfo($src, PATHINFO_EXTENSION) ?: 'jpg');
        $path = 'prisoners/'.Str::slug($prisoner->name).'.'.$ext;
        Storage::disk('public')->put($path, (string) file_get_contents($src));
        $prisoner->photo = $path;
        $prisoner->save();
        $this->info("  {$prisoner->name} ← {$path}");

        return true;
    }
}
