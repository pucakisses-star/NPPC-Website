<?php

namespace App\Console\Commands;

use App\Models\CalendarEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Add a May 4 calendar entry for the original 13 Freedom Riders'
 * 1961 departure from Washington, D.C., and attach the photo from
 * the @hkb73 2026-05-04 tweet. Existing 5/4 Kent State entry is
 * left in place — this adds a second entry for the day rather
 * than replacing it.
 *
 * Idempotent: identifies the entry by (month, day, title-prefix).
 */
final class AddFreedomRiders1961Calendar extends Command {
    protected $signature = 'calendar:add-freedom-riders-1961';
    protected $description = 'Add May 4 1961 Freedom Riders calendar entry with photo';

    private const IMAGE_URL    = 'https://pbs.twimg.com/media/HHe-Rp5WgAAaAEb.jpg';
    private const STORAGE_PATH = 'calendar/freedom-riders-1961.jpg';
    private const TITLE        = 'Original 13 Freedom Riders leave Washington, D.C. for New Orleans';

    public function handle(): int {
        // Download the photo into the public disk.
        if (! Storage::disk('public')->exists(self::STORAGE_PATH)) {
            $this->line('fetch '.self::IMAGE_URL);
            try {
                $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; NPPC-Archive/1.0)'])
                    ->timeout(60)
                    ->get(self::IMAGE_URL);
                if (! $resp->successful() || strlen($resp->body()) < 5000) {
                    $this->error('Image download failed (HTTP '.$resp->status().') — proceeding without image.');
                } else {
                    Storage::disk('public')->put(self::STORAGE_PATH, $resp->body());
                    $this->info('Saved image to '.self::STORAGE_PATH);
                }
            } catch (\Throwable $e) {
                $this->error('Fetch error: '.$e->getMessage().' — proceeding without image.');
            }
        } else {
            $this->line('image exists at '.self::STORAGE_PATH);
        }

        $imagePath = Storage::disk('public')->exists(self::STORAGE_PATH)
            ? self::STORAGE_PATH
            : null;

        // Upsert by (month, day, title prefix) so re-runs don't duplicate
        // and the existing Kent State entry on 5/4 stays untouched.
        $existing = CalendarEntry::where('month', 5)
            ->where('day', 4)
            ->where('title', 'like', 'Original 13 Freedom Riders%')
            ->first();

        $payload = [
            'month'       => 5,
            'day'         => 4,
            'year'        => 1961,
            'title'       => self::TITLE,
            'description' => "On May 4, 1961 the original 13 Freedom Riders — seven Black and six white civil-rights activists organized by the Congress of Racial Equality (CORE) — departed Washington, D.C. on two Greyhound and Trailways buses bound for New Orleans. The interracial group rode together in defiance of Jim Crow segregation at southern bus terminals and on interstate transportation, testing the U.S. Supreme Court's recent Boynton v. Virginia (1960) ruling that segregation in interstate travel facilities was unconstitutional. The Riders faced mob violence in Anniston (May 14, where one bus was firebombed), Birmingham, and Montgomery, drew federal intervention from Attorney General Robert F. Kennedy, and were arrested en masse in Jackson, Mississippi. Hundreds of additional Riders followed through the summer; the campaign forced the Interstate Commerce Commission to issue an enforceable desegregation order in September 1961.",
            'image'       => $imagePath,
            'published'   => true,
        ];

        if ($existing) {
            $existing->update($payload);
            $this->info('Updated 5/4 Freedom Riders entry (id '.$existing->id.').');
        } else {
            CalendarEntry::create($payload);
            $this->info('Added 5/4 Freedom Riders entry.');
        }

        return self::SUCCESS;
    }
}
