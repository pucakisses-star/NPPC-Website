<?php

namespace App\Console\Commands;

use App\Models\CalendarEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Attach the Working Class History photo from
 * @wrkclasshistory's 2026-03-28 tweet to the existing March 28
 * 1919 calendar entry (Arkansas anti-anarchism law). Image is
 * stored on the public disk; calendar.image stores the relative
 * path. Idempotent.
 */
final class AttachArkansas1919CalendarPhoto extends Command {
    protected $signature = 'calendar:attach-arkansas-1919-photo';
    protected $description = 'Attach the WCH photo to the March 28 1919 Arkansas anti-anarchism calendar entry';

    private const IMAGE_URL = 'https://pbs.twimg.com/media/HEfiH0oXMAAmnPr.jpg';
    private const STORAGE_PATH = 'calendar/arkansas-anti-anarchism-1919.jpg';

    public function handle(): int {
        $entry = CalendarEntry::where('month', 3)
            ->where('day', 28)
            ->where('title', 'like', '%Arkansas%')
            ->first();

        if (! $entry) {
            $this->error('No March 28 Arkansas calendar entry found. Run calendar:seed-from-wch or seed-extra first.');
            return self::FAILURE;
        }

        if (! Storage::disk('public')->exists(self::STORAGE_PATH)) {
            $this->line('fetch '.self::IMAGE_URL);
            try {
                $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; NPPC-Archive/1.0)'])
                    ->timeout(60)
                    ->get(self::IMAGE_URL);
                if (! $resp->successful() || strlen($resp->body()) < 1000) {
                    $this->error('Download failed (HTTP '.$resp->status().') — leaving entry image unset.');
                    return self::FAILURE;
                }
                Storage::disk('public')->put(self::STORAGE_PATH, $resp->body());
                $this->info('Saved image to '.self::STORAGE_PATH);
            } catch (\Throwable $e) {
                $this->error('Fetch error: '.$e->getMessage());
                return self::FAILURE;
            }
        } else {
            $this->line('exists '.self::STORAGE_PATH);
        }

        if ($entry->image === self::STORAGE_PATH) {
            $this->line('Entry already linked.');
        } else {
            $entry->image = self::STORAGE_PATH;
            $entry->save();
            $this->info('Linked image to entry #'.$entry->id.' ('.$entry->title.')');
        }

        return self::SUCCESS;
    }
}
