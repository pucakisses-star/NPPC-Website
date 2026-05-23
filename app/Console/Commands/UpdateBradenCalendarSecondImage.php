<?php

namespace App\Console\Commands;

use App\Models\CalendarEntry;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Swap the May 22, 1954 Braden calendar entry's image to the second
 * photo from the source tweet, and link it to the Carl Braden prisoner
 * row so the day-view also surfaces his mugshot as the prisoner avatar.
 *
 * Result on /calendar?month=5&day=22&view=day:
 *   - large hero image = second tweet photo (this command)
 *   - prisoner avatar  = the mugshot (set on the Prisoner row earlier)
 *
 * Idempotent.
 */
final class UpdateBradenCalendarSecondImage extends Command {
    protected $signature = 'calendar:update-braden-second-image';
    protected $description = 'Add second Braden tweet photo to calendar entry + link entry to prisoner';

    private const IMAGE_SRC = 'https://pbs.twimg.com/media/HIzJYQEWgAAt2sR?format=jpg&name=medium';
    private const IMAGE_PATH = 'calendar/carl-braden-1954-second.jpg';

    public function handle(): int {
        $disk = Storage::disk('public');

        if (! $disk->exists(self::IMAGE_PATH)) {
            try {
                $resp = Http::withHeaders(['User-Agent' => 'NPPC-Archive/1.0'])
                    ->timeout(60)
                    ->get(self::IMAGE_SRC);
                if (! $resp->successful() || strlen($resp->body()) < 5000) {
                    $this->error('Image download failed (status '.$resp->status().').');
                    return self::FAILURE;
                }
                $disk->put(self::IMAGE_PATH, $resp->body());
                $this->info('Downloaded image to '.self::IMAGE_PATH);
            } catch (\Throwable $e) {
                $this->error('Image fetch error: '.$e->getMessage());
                return self::FAILURE;
            }
        } else {
            $this->info('Image already cached at '.self::IMAGE_PATH);
        }

        $title = 'Carl & Anne Braden buy a home for a Black family in Louisville';
        $entry = CalendarEntry::query()
            ->where('month', 5)
            ->where('day', 22)
            ->where('year', 1954)
            ->where('title', $title)
            ->first();

        if (! $entry) {
            $this->error('Calendar entry not found. Run calendar:add-braden-1954 first.');
            return self::FAILURE;
        }

        $entry->image = self::IMAGE_PATH;

        $prisoner = Prisoner::query()->where('name', 'Carl Braden')->first();
        if ($prisoner) {
            $entry->prisoner_id = $prisoner->id;
            $this->info('Linked calendar entry to prisoner: '.$prisoner->name);
        } else {
            $this->warn('Prisoner "Carl Braden" not found — image updated, prisoner link unchanged.');
        }

        $entry->save();
        $this->info('Updated calendar entry.');
        return self::SUCCESS;
    }
}
