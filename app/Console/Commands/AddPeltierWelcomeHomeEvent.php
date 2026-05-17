<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Add the February 19, 2025 NDN Collective Welcome Home event for
 * Leonard Peltier — Sky Dancer Event Center, Belcourt, ND — the
 * homecoming gathering held one day after his release from federal
 * prison following Biden's January 20, 2025 clemency commutation.
 * Photo from the @ndncollective 2025-02-18 tweet.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddPeltierWelcomeHomeEvent extends Command {
    protected $signature = 'events:add-peltier-welcome-home';
    protected $description = 'Add the Feb 19, 2025 Leonard Peltier welcome home event in Belcourt, ND';

    private const SLUG      = 'leonard-peltier-welcome-home-belcourt-nd-2025-02-19';
    private const IMAGE_URL = 'https://pbs.twimg.com/media/GkGphxyXIAIzm6O.jpg';

    public function handle(): int {
        // Pull the announcement graphic from the NDN Collective tweet.
        $imagePath = 'events/'.self::SLUG.'.jpg';
        $remoteFallback = self::IMAGE_URL;
        try {
            if (! Storage::disk('public')->exists($imagePath)) {
                $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; NPPC-Archive/1.0)'])
                    ->timeout(60)
                    ->get(self::IMAGE_URL);
                if ($resp->successful() && strlen($resp->body()) > 5000) {
                    Storage::disk('public')->put($imagePath, $resp->body());
                    $this->info('Saved image to '.$imagePath);
                } else {
                    $imagePath = $remoteFallback;
                    $this->warn('Image download failed — using remote URL.');
                }
            } else {
                $this->line('image exists at '.$imagePath);
            }
        } catch (\Throwable $e) {
            $imagePath = $remoteFallback;
            $this->warn('Image fetch error: '.$e->getMessage());
        }

        $description = "NDN Collective and the Turtle Mountain Chippewa community held a celebratory welcome-home event and community feed at the Sky Dancer Event Center in Belcourt, North Dakota at 12:00 p.m. CST on Wednesday, February 19, 2025 — one day after Leonard Peltier's release from FCI Coleman following President Biden's January 20, 2025 commutation of the remainder of his federal sentence.\n\n"
            ."Approximately 500 people gathered for the event, which featured a community feast, a drum circle, dancers, and the presentation to Peltier of an eagle-feather staff that supporters had carried to Washington, D.C. and across Indian Country throughout the decades-long campaign for his release, plus a traditional star quilt. The event marked the end of 49 years of federal incarceration for the longest-held Indigenous political prisoner in U.S. history.\n\n"
            ."Live-streamed on NDN Collective's Facebook, YouTube, and LinkedIn channels. Coverage and photos: Last Real Indians, ICT News, KFYR-TV, InForum, AP/Washington Times. Press release: http://ndnco.cc/lphomepr";

        $data = [
            'title'       => 'Welcome Home, Leonard Peltier — Sky Dancer Event Center, Belcourt, ND',
            'description' => $description,
            'body'        => null,
            'image'       => $imagePath,
            'event_date'  => '2025-02-19',
            'time'        => '12:00 p.m. CST',
            'location'    => 'Sky Dancer Event Center, Belcourt, North Dakota (Turtle Mountain Chippewa Reservation)',
            'event_url'   => 'https://ndncollective.org/ndn-collective-to-host-welcome-home-event-for-leonard-peltier/',
            'series'      => 'Leonard Peltier Homecoming',
            'published'   => true,
        ];

        $existing = Event::where('slug', self::SLUG)->first();
        if ($existing) {
            $existing->update($data);
            $this->info('Updated event: '.$data['title']);
        } else {
            Event::create(['slug' => self::SLUG] + $data);
            $this->info('Created event: '.$data['title']);
        }

        return self::SUCCESS;
    }
}
