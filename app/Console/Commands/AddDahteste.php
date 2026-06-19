<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Adds Dahteste — the Chokonen Chiricahua Apache warrior, scout, and
 * interpreter who helped negotiate Geronimo's 1886 surrender and was then
 * held as a U.S. Army prisoner of war for roughly 27 years (Fort Marion, FL,
 * then Fort Sill, OK) before her release to the Mescalero Apache Reservation.
 * Her 1886 photograph (public domain, via Wikimedia Commons) is committed at
 * database/data/photos/dahteste.jpg and attached on every run. Idempotent.
 */
final class AddDahteste extends Command
{
    protected $signature = 'prisoners:add-dahteste';

    protected $description = 'Add Dahteste (Chiricahua Apache POW, ~27 years) with her 1886 photo';

    public function handle(): int
    {
        $existing = Prisoner::withUnderReview()->where('name', 'Dahteste')->first();

        if ($existing) {
            $this->warn('Dahteste already exists — skipping record creation.');
            $this->attachLocalPhoto($existing, 'photos/dahteste.jpg');

            return self::SUCCESS;
        }

        $prisoner = DB::transaction(function () {
            $prisoner = Prisoner::create([
                'name' => 'Dahteste',
                'first_name' => 'Dahteste',
                'gender' => 'Female',
                'race' => 'Native American',
                'state' => 'New Mexico',
                'era' => '1800s',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'affiliation' => ['Chiricahua Apache'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "Dahteste (c. 1860–1955) was a Chokonen Chiricahua Apache warrior, scout, and interpreter — a comrade of Lozen and Geronimo who, fluent in English, served as a messenger and mediator and was instrumental in negotiating Geronimo's final surrender to the U.S. Cavalry in 1886. Despite her role in the negotiations, she was taken with the rest of the band as a U.S. Army prisoner of war: she spent about eight years at Fort Marion in St. Augustine, Florida — where she survived pneumonia and tuberculosis — and roughly nineteen more at Fort Sill, Oklahoma, some twenty-seven years of confinement in all. She was finally freed around 1913 and lived out the rest of her life at Whitetail on the Mescalero Apache Reservation in New Mexico.",
            ]);

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges' => "Held as a U.S. Army prisoner of war following the 1886 surrender of Geronimo's Chiricahua Apache band (no criminal charge or trial)",
                'incarceration_date' => '1886-09-08',
                'release_date' => '1913-04-02',
                'convicted' => 'Held as a prisoner of war — never charged or tried',
                'sentence' => 'About 27 years as a POW: ~8 years at Fort Marion (St. Augustine, FL), then ~19 years at Fort Sill (OK); released c. 1913 to the Mescalero Apache Reservation, New Mexico',
            ]);

            return $prisoner;
        });

        $this->info('Added Dahteste.');
        $this->attachLocalPhoto($prisoner, 'photos/dahteste.jpg');

        return self::SUCCESS;
    }

    /**
     * Copy a committed local photo onto the public disk and set it as the
     * prisoner's photo (re-synced each run). Source is her 1886 portrait from
     * Wikimedia Commons (public domain).
     */
    private function attachLocalPhoto(Prisoner $prisoner, string $relative): void
    {
        $src = database_path('data/'.$relative);
        if (! is_file($src)) {
            $this->warn("  Local photo not found: {$relative}");

            return;
        }

        $ext = strtolower(pathinfo($src, PATHINFO_EXTENSION) ?: 'jpg');
        $path = 'prisoners/'.Str::slug($prisoner->name).'.'.$ext;
        Storage::disk('public')->put($path, (string) file_get_contents($src));
        $prisoner->photo = $path;
        $prisoner->save();
        $this->info("  Photo set from file: {$path}");
    }
}
