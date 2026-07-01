<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AddIceProtestDashboardLinks extends Command {
    protected $signature   = 'dashboard:add-ice-protest-links';
    protected $description = 'Add three 2025 ICE/protest-related dashboard links';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'A federal CBP officer was charged with assault and criminal mischief after video showed him grabbing Durango protester Franci Stagi by the hair and pushing her to the ground outside an ICE facility',
                'url'            => 'https://www.independent.co.uk/news/world/americas/crime/immigration-officer-assault-durango-protest-ice-b2963191.html',
                'source'         => 'The Independent',
                'category'       => 'prosecution',
                'published_at'   => '2025-10-28',
                'location_label' => 'Durango, Colorado',
                'lat'            => 37.2753,
                'lng'            => -107.8801,
            ],
            [
                'title'          => 'Portland prosecutors dropped a felony assault charge against ICE protester Lucy Shepherd after video showed she only "brushed" an officer\'s arm — not the "strike" police reported',
                'url'            => 'https://www.oregonlive.com/crime/2025/12/assault-charge-dismissed-against-ice-protester-after-video-shows-no-attack-on-officer-in-portland.html',
                'source'         => 'OregonLive',
                'category'       => 'prosecution',
                'published_at'   => '2025-12-01',
                'location_label' => 'Portland, Oregon',
                'lat'            => 45.5234,
                'lng'            => -122.6762,
            ],
            [
                'title'          => '8 Kansans (Free State Advocates) were arrested at the Dirksen Senate Building cafeteria during a 3-day DC housing protest against proposed HUD cuts; most released within hours, one held overnight',
                'url'            => 'https://yellowscene.com/2025/09/21/8-kansans-arrested-3-days-of-protest-in-d-c/',
                'source'         => 'Yellow Scene',
                'category'       => 'arrest',
                'published_at'   => '2025-09-12',
                'location_label' => 'Washington, D.C.',
                'lat'            => 38.8928,
                'lng'            => -77.0044,
            ],
        ];

        $created = 0;
        foreach ($cases as $case) {
            $link = DashboardLink::firstOrCreate(
                ['url' => $case['url']],
                array_merge($case, ['published_at' => Carbon::parse($case['published_at'])]),
            );

            if ($link->wasRecentlyCreated) {
                $created++;
                $this->info("Added: {$case['title']}");
            } else {
                $this->line("Skipped (already present): {$case['title']}");
            }
        }

        $this->info("Done. {$created} new link(s) added; " . (count($cases) - $created) . ' already present.');

        return self::SUCCESS;
    }
}
