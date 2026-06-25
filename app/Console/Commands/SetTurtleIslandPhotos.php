<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets profile photos for the four Turtle Island Liberation Front defendants
 * charged in the alleged New Year's Eve bombing plot. The booking/DMV photos
 * were taken from the KMPH news report, cropped to clean 3:4 portraits (the
 * source assets carry a blurred-fill border that has been trimmed off), and
 * committed under public/images/prisoners/. This command copies each onto the
 * public disk (where prisoner photos are served from) and points the record at
 * it. Identities were verified individually against labeled portraits before
 * mapping each photo to a defendant. Idempotent; only updates prisoners that
 * exist. Micah Legnon — arrested separately in Louisiana — is not pictured in
 * that report and is intentionally omitted.
 */
final class SetTurtleIslandPhotos extends Command
{
    protected $signature = 'prisoners:set-turtle-island-photos';

    protected $description = 'Set profile photos for the four Turtle Island Liberation Front defendants from committed cropped images';

    /** slug => committed source image under public/ */
    private const PHOTOS = [
        'zachary-page' => 'images/prisoners/zachary-page.jpg',
        'audrey-carroll' => 'images/prisoners/audrey-carroll.jpg',
        'tina-lai' => 'images/prisoners/tina-lai.jpg',
        'dante-gaffield' => 'images/prisoners/dante-gaffield.jpg',
    ];

    public function handle(): int
    {
        $set = 0;
        $missing = 0;

        foreach (self::PHOTOS as $slug => $source) {
            $sourcePath = public_path($source);
            if (! is_file($sourcePath)) {
                $this->error('Source image not found: public/'.$source);

                continue;
            }

            $dest = 'prisoners/'.basename($source);
            Storage::disk('public')->put($dest, file_get_contents($sourcePath));

            $prisoner = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $prisoner) {
                $this->warn("Prisoner not found for slug '{$slug}' — skipped (photo copied to {$dest}).");
                $missing++;

                continue;
            }

            $prisoner->photo = $dest;
            $prisoner->save();
            $set++;
            $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");
        }

        $this->info("\nDone. {$set} photo(s) set".($missing ? ", {$missing} prisoner(s) missing." : '.'));

        return self::SUCCESS;
    }
}
