<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Two more anti-ICE arrest events for the map: the roughly 100 clergy arrested
 * while praying at the entrances of Minneapolis-St. Paul International Airport
 * during the January 2026 strike against Operation Metro Surge, and the federal
 * prosecution of nine Spokane protesters (including former city council
 * president Ben Stuckart) who blocked an ICE detainee transport in June 2025.
 *
 * Same shape as the other add-*-events commands: every source URL becomes a
 * newswire item, one representative source per event carries the coordinates
 * (the marker), and rows are keyed by URL via updateOrCreate so the command is
 * idempotent. Manage them in /admin afterwards.
 *
 * Run on the server: php artisan dashboard:add-msp-spokane-events
 */
final class AddMspAndSpokaneEvents extends Command {
    protected $signature = 'dashboard:add-msp-spokane-events';
    protected $description = 'Add the MSP Airport clergy arrest and Spokane federal ICE-protest case to the dashboard';

    /**
     * [title, url, source, date, lat, lng, location_label]. lat/lng/label are
     * null for additional sources covering an event already pinned above them.
     */
    private array $links = [
        // ── Minneapolis MN · Minneapolis-St. Paul International Airport · Jan 23, 2026 ──
        ['About 100 clergy arrested at anti-ICE protest at MSP Airport', 'https://www.cbsnews.com/minnesota/news/clergy-members-arrested-minneapolis-st-paul-international-airport/', 'CBS Minnesota', '2026-01-23', 44.8780191, -93.2209281, 'Minneapolis-St. Paul International Airport, MN'],
        ['100 clergy arrested at airport protest as Minnesotans strike against ICE', 'https://www.spokesman.com/stories/2026/jan/23/100-clergy-arrested-at-airport-protest-as-minnesot/', 'The Spokesman-Review', '2026-01-23', null, null, null],

        // ── Spokane WA · Thomas S. Foley U.S. Courthouse · Jun 11, 2025 (blockade, later federal charges) ──
        ['Nine Spokane ICE-protesters, including ex-council president, federally charged', 'https://www.krem.com/article/news/local/former-spokane-city-council-ben-stuckart-federally-indicted-ice-protests/293-b7211c4d-12e8-407d-b3d9-17f42c3cf6f1', 'KREM', '2025-06-11', 47.6585808, -117.4260620, 'Thomas S. Foley U.S. Courthouse, Spokane, WA'],
        ['Federal agents arrest 9 over Spokane ICE protest, including ex-council president', 'https://www.democracynow.org/2025/7/16/headlines/federal_agents_arrest_9_over_spokane_ice_protests_including_former_city_council_president', 'Democracy Now!', '2025-06-11', null, null, null],
    ];

    public function handle(): int {
        $created = 0;
        $updated = 0;
        $markers = 0;

        foreach ($this->links as [$title, $url, $source, $date, $lat, $lng, $label]) {
            $link = DashboardLink::updateOrCreate(
                ['url' => $url],
                [
                    'title' => $title,
                    'source' => $source,
                    'published_at' => Carbon::parse($date . ' 09:00'),
                    'lat' => $lat,
                    'lng' => $lng,
                    'location_label' => $label,
                ],
            );

            $link->wasRecentlyCreated ? $created++ : $updated++;
            if ($lat !== null) {
                $markers++;
            }
        }

        $this->info("Done. {$created} created, {$updated} updated — " . count($this->links) . " newswire items, {$markers} map markers.");

        return self::SUCCESS;
    }
}
