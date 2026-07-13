<?php

namespace App\Console\Commands;

use App\Models\Petition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Gives every imageless petition a photograph. Images ship in
 * database/data/photos/petitions/ (named by petition slug; sources and
 * licenses in CREDITS.md there) and install to the public disk. Petitions
 * that already have an image are never touched; idempotent and re-runnable.
 */
class AddPetitionImages extends Command {
    protected $signature = 'petitions:add-images';
    protected $description = 'Attach photos to petitions that are missing one';

    public function handle(): int {
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
