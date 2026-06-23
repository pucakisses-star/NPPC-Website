<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Downloads and sets the profile photo for Pam Fadem from a public portrait
 * hosted by the University of San Francisco (a speaker-series page). Stores it on
 * the public disk at prisoners/pam-fadem.jpg and points her record at it.
 *
 * Idempotent: if the photo is already cached and set, it does nothing. Pass
 * --overwrite to force a fresh download. Run prisoners:add-pam-fadem first so the
 * record exists.
 */
final class SetPamFademPhoto extends Command
{
    protected $signature = 'prisoners:set-pam-fadem-photo {--overwrite : Re-download even if a photo is already cached}';

    protected $description = "Download and set Pam Fadem's profile photo";

    private const IMAGE_SRC = 'https://myusf.usfca.edu/sites/default/files/users/tdeocampo/OLD%20Barnett%20Speakers%202026/Pam%20Fadem.jpg';

    private const PHOTO_PATH = 'prisoners/pam-fadem.jpg';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $overwrite = (bool) $this->option('overwrite');

        if ($overwrite || ! $disk->exists(self::PHOTO_PATH)) {
            try {
                $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (NPPC-Archive/1.0)'])
                    ->timeout(60)
                    ->get(self::IMAGE_SRC);
            } catch (\Throwable $e) {
                $this->error('Photo fetch error: '.$e->getMessage());

                return self::FAILURE;
            }

            if (! $resp->successful() || strlen($resp->body()) < 5000) {
                $this->error('Photo download failed (status '.$resp->status().', '.strlen($resp->body()).' bytes).');

                return self::FAILURE;
            }

            $disk->put(self::PHOTO_PATH, $resp->body());
            $this->info('Downloaded photo to '.self::PHOTO_PATH);
        } else {
            $this->info('Photo already cached at '.self::PHOTO_PATH.' (use --overwrite to refresh)');
        }

        $prisoner = Prisoner::withUnderReview()->where('slug', 'pam-fadem')->first();
        if (! $prisoner) {
            $this->error('Prisoner "pam-fadem" not found. Run prisoners:add-pam-fadem first.');

            return self::FAILURE;
        }

        if ($prisoner->photo === self::PHOTO_PATH && ! $overwrite) {
            $this->info('Pam Fadem already points at '.self::PHOTO_PATH.'; nothing to do.');

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO_PATH;
        $prisoner->save();
        $this->info('Set Pam Fadem photo → '.self::PHOTO_PATH);

        return self::SUCCESS;
    }
}
