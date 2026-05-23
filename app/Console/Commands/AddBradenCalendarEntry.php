<?php

namespace App\Console\Commands;

use App\Models\CalendarEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Adds a May 22, 1954 calendar entry commemorating the Carl & Anne
 * Braden case. Carl Braden was sentenced to 15 years in prison on
 * sedition charges after buying a Louisville home in his own name
 * and transferring it to a Black family (the Wades) at a time when
 * real estate brokers refused to sell to Black buyers in white
 * neighborhoods. The home was firebombed; authorities prosecuted
 * Braden — not the bombers — for "inciting violence." He served 8
 * months before his conviction was overturned. Anne Braden remained
 * close to Dr. King and is named in his "Letter from a Birmingham
 * Jail."
 *
 * Idempotent — matches on month/day/year/title.
 */
final class AddBradenCalendarEntry extends Command {
    protected $signature = 'calendar:add-braden-1954';
    protected $description = 'Add May 22, 1954 Carl Braden calendar entry';

    private const IMAGE_SRC = 'https://pbs.twimg.com/media/HIzJYQBWAAA9ay2?format=jpg&name=large';
    private const IMAGE_PATH = 'calendar/carl-braden-1954-mugshot.jpg';

    public function handle(): int {
        $imagePath = null;
        if (Storage::disk('public')->exists(self::IMAGE_PATH)) {
            $imagePath = self::IMAGE_PATH;
            $this->info('Image already cached at '.self::IMAGE_PATH);
        } else {
            try {
                $resp = Http::withHeaders(['User-Agent' => 'NPPC-Archive/1.0'])
                    ->timeout(60)
                    ->get(self::IMAGE_SRC);
                if ($resp->successful() && strlen($resp->body()) > 5000) {
                    Storage::disk('public')->put(self::IMAGE_PATH, $resp->body());
                    $imagePath = self::IMAGE_PATH;
                    $this->info('Downloaded image to '.self::IMAGE_PATH);
                } else {
                    $this->warn('Image download failed (status '.$resp->status().'); proceeding without image.');
                }
            } catch (\Throwable $e) {
                $this->warn('Image fetch error: '.$e->getMessage());
            }
        }

        $title = 'Carl & Anne Braden buy a home for a Black family in Louisville';
        $description = "In 1954, Carl and Anne Braden — white anti-racist organizers in Louisville, Kentucky — bought a home in their own names and transferred it to their friends Andrew and Charlotte Wade, a Black family. Real estate brokers had refused to sell to the Wades, a U.S. veteran's family, in any of the city's white neighborhoods.\n\nThe reaction was immediate. Shots were fired into the home. Six weeks later, the house was destroyed by dynamite. Instead of pursuing the bombers, Kentucky prosecutors went after Carl Braden — charging him with sedition for \"inciting violence and civil unrest\" by helping a Black family buy a home.\n\nCarl Braden was convicted and sentenced to 15 years in prison. He served roughly eight months before the conviction was overturned. He continued his civil-rights organizing for decades afterward. Anne Braden remained close to Dr. Martin Luther King Jr. and is one of the few white organizers named in his \"Letter from a Birmingham Jail\" (1963).";

        $payload = [
            'month' => 5,
            'day' => 22,
            'year' => 1954,
            'title' => $title,
            'description' => $description,
            'image' => $imagePath,
            'published' => true,
        ];

        $existing = CalendarEntry::query()
            ->where('month', 5)
            ->where('day', 22)
            ->where('year', 1954)
            ->where('title', $title)
            ->first();

        if ($existing) {
            $existing->update($payload);
            $this->info('Updated existing calendar entry.');
        } else {
            CalendarEntry::create($payload);
            $this->info('Created calendar entry.');
        }

        return self::SUCCESS;
    }
}
