<?php

namespace App\Console\Commands;

use App\Models\CalendarEntry;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Add a March 25 calendar entry for the 1977 conviction of Assata
 * Shakur (JoAnne Chesimard) in the Middlesex County, NJ trial for
 * the death of NJ State Trooper Werner Foerster. Image sourced
 * from @ADayIn1920's 2026-03-26 tweet. Existing March 25 Viola
 * Liuzzo entry is left in place — this adds a second entry for
 * the day rather than replacing it.
 *
 * Links the calendar entry to Assata Shakur's existing prisoner
 * record so the day-view shows the prisoner card.
 *
 * Idempotent via (month, day, title-prefix) upsert.
 */
final class AddAssataConvictionCalendar extends Command {
    protected $signature = 'calendar:add-assata-conviction';
    protected $description = 'Add March 25 1977 Assata Shakur conviction calendar entry with photo';

    private const IMAGE_URL    = 'https://pbs.twimg.com/media/HETSHy7bYAIoFUx.jpg';
    private const STORAGE_PATH = 'calendar/assata-shakur-conviction-1977.jpg';
    private const TITLE        = 'Assata Shakur convicted by all-white New Jersey jury';

    public function handle(): int {
        // Pull the photo onto the public disk.
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

        // Link to her prisoner record if present.
        $assata = Prisoner::where('slug', 'joanne-d-chesimard-assata-shakur')
            ->orWhere('name', 'Assata Shakur')
            ->orWhere('name', 'like', '%Chesimard%')
            ->first();

        $payload = [
            'month'       => 3,
            'day'         => 25,
            'year'        => 1977,
            'title'       => self::TITLE,
            'description' => "On March 25, 1977, after a six-week trial in Middlesex County, New Jersey, an all-white jury convicted Assata Shakur (legal name JoAnne Deborah Chesimard) of first-degree murder for the May 2, 1973 shooting death of New Jersey State Trooper Werner Foerster on the New Jersey Turnpike, plus seven additional felony counts. She was the only one of the three Black Liberation Army members in the car (Zayd Malik Shakur, killed in the shootout; Sundiata Acoli) to be tried for Foerster's death. The verdict came despite forensic evidence — including neutron-activation tests showing no gunshot residue on her hands and medical testimony that her wounds (a bullet through one arm, another lodged in her chest) made it nearly impossible for her to have fired the weapon attributed to her. She was sentenced one month later to life plus 33 years.\n\nShe spent the next two years in maximum-security custody, much of it in solitary confinement and in a men's prison, before her November 2, 1979 liberation from the Clinton Correctional Facility for Women by Black Liberation Army comrades. After several years underground she received political asylum in Cuba in 1984, where she lived as a writer and movement elder until her death on September 25, 2025.",
            'image'       => $imagePath,
            'published'   => true,
            'prisoner_id' => $assata?->id,
        ];

        $existing = CalendarEntry::where('month', 3)
            ->where('day', 25)
            ->where('title', 'like', 'Assata Shakur convicted%')
            ->first();

        if ($existing) {
            $existing->update($payload);
            $this->info('Updated 3/25 Assata conviction entry (id '.$existing->id.').');
        } else {
            CalendarEntry::create($payload);
            $this->info('Added 3/25 Assata conviction entry.');
        }

        return self::SUCCESS;
    }
}
