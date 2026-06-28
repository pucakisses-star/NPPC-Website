<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets profile photos for defendants in the Prairieland Detention Center case
 * (the July 4, 2025 protest in Alvarado, Texas). Photos are sourced from the
 * DFW Support Committee's official "Meet the Defendants" gallery
 * (prairielanddefendants.com), which publishes consented, captioned portraits.
 * Each committed source image was cropped to a head-and-shoulders portrait.
 *
 * Notes on specific records:
 *  - Benjamin Song's gallery image is a couple photo; cropped to him.
 *  - Bradford Morris is featured under the name she goes by, "Meagan Morris."
 *  - Elizabeth Soto and Ines Soto are a couple and the committee uses a single
 *    shared couple photo for both profiles, so the same image is set on both
 *    records (it cannot be reliably split into individual portraits).
 *
 * Not set here (no usable/consented photo found):
 *  - Cameron Arnold ("Autumn Hill"): the only committee image is a two-person
 *    photo and it is not possible to tell which subject is her.
 *  - The cooperating co-defendants (John Thomas, Lynette Sharp, Nathan Baumann,
 *    Seth Sikes, Susan Kent): not featured by the committee, and no booking
 *    photo was publicly released.
 *
 * Idempotent; matches each record by slug. Safe to re-run.
 */
final class SetPrairielandPhotos extends Command
{
    protected $signature = 'prisoners:set-prairieland-photos';

    protected $description = 'Set profile photos for Prairieland Detention Center case defendants';

    /** slug => committed source filename under public/images/prisoners/ */
    private const PHOTOS = [
        'benjamin-song' => 'benjamin-song.jpg',
        'maricela-rueda' => 'maricela-rueda.jpg',
        'savanna-batten' => 'savanna-batten.jpg',
        'zachary-evetts' => 'zachary-evetts.jpg',
        'bradford-morris' => 'bradford-morris.jpg',
        'elizabeth-soto' => 'elizabeth-soto.jpg',
        'ines-soto' => 'ines-soto.jpg',
    ];

    public function handle(): int
    {
        $set = 0;
        $missingFiles = [];
        $missingRecords = [];

        foreach (self::PHOTOS as $slug => $file) {
            $source = public_path('images/prisoners/'.$file);
            if (! is_file($source)) {
                $missingFiles[] = $file;
                $this->error("Source image not found: public/images/prisoners/{$file}");

                continue;
            }

            $stored = 'prisoners/'.$file;
            Storage::disk('public')->put($stored, file_get_contents($source));

            $prisoner = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $prisoner) {
                $missingRecords[] = $slug;
                $this->warn("No record for slug '{$slug}' — photo copied to disk but not attached.");

                continue;
            }

            $prisoner->photo = $stored;
            $prisoner->save();
            $set++;
            $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");
        }

        $this->newLine();
        $this->info("Done. Photos set: {$set}.");
        if ($missingFiles) {
            $this->warn('Missing source files: '.implode(', ', $missingFiles));
        }
        if ($missingRecords) {
            $this->warn('Missing records (slugs): '.implode(', ', $missingRecords));
        }

        return self::SUCCESS;
    }
}
