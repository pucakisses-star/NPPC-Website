<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Sets the photo for the Carl Braden prisoner row. Reuses the same
 * 1954 mugshot image already downloaded for the calendar entry —
 * if that file already exists at calendar/carl-braden-1954-mugshot.jpg
 * we copy it into the prisoners directory rather than redownloading.
 *
 * Idempotent.
 */
final class SetCarlBradenPhoto extends Command {
    protected $signature = 'archive:set-carl-braden-photo';
    protected $description = 'Set the photo on Carl Braden\'s prisoner profile';

    private const IMAGE_SRC = 'https://pbs.twimg.com/media/HIzJYQBWAAA9ay2?format=jpg&name=large';
    private const PHOTO_PATH = 'prisoners/carl-braden.jpg';
    private const CACHED_CALENDAR_PATH = 'calendar/carl-braden-1954-mugshot.jpg';

    public function handle(): int {
        $disk = Storage::disk('public');

        if (! $disk->exists(self::PHOTO_PATH)) {
            // Prefer the already-cached calendar copy.
            if ($disk->exists(self::CACHED_CALENDAR_PATH)) {
                $disk->put(self::PHOTO_PATH, $disk->get(self::CACHED_CALENDAR_PATH));
                $this->info('Copied cached calendar image to '.self::PHOTO_PATH);
            } else {
                try {
                    $resp = Http::withHeaders(['User-Agent' => 'NPPC-Archive/1.0'])
                        ->timeout(60)
                        ->get(self::IMAGE_SRC);
                    if (! $resp->successful() || strlen($resp->body()) < 5000) {
                        $this->error('Photo download failed (status '.$resp->status().').');
                        return self::FAILURE;
                    }
                    $disk->put(self::PHOTO_PATH, $resp->body());
                    $this->info('Downloaded photo to '.self::PHOTO_PATH);
                } catch (\Throwable $e) {
                    $this->error('Photo fetch error: '.$e->getMessage());
                    return self::FAILURE;
                }
            }
        } else {
            $this->info('Photo already cached at '.self::PHOTO_PATH);
        }

        $prisoner = Prisoner::query()->where('name', 'Carl Braden')->first();
        if (! $prisoner) {
            $this->error('Prisoner "Carl Braden" not found. Run archive:add-carl-braden first.');
            return self::FAILURE;
        }

        $prisoner->photo = self::PHOTO_PATH;
        $prisoner->save();
        $this->info('Set Carl Braden photo → '.self::PHOTO_PATH);
        return self::SUCCESS;
    }
}
