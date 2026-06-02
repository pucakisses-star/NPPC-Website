<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * More depth on the immigration-crackdown map: the multi-night clashes at
 * Newark's Delaney Hall ICE jail (May 2026), the 11 arrested at the Bishop
 * Henry Whipple Federal Building in Minneapolis the day after ICE killed Renee
 * Good (Jan 2026), and the 14 "suburban moms" arrested in a sit-in at the
 * Broadview ICE facility (Nov 2025). These reuse venues already on the map but
 * mark distinct, later events; each is pinned at its exact venue.
 *
 * Same shape as the other add-*-events commands: every source URL becomes a
 * newswire item, one representative source per event carries the coordinates
 * (the marker), and rows are keyed by URL via updateOrCreate so the command is
 * idempotent. Manage them in /admin afterwards.
 *
 * Run on the server: php artisan dashboard:add-depth-events-2
 */
final class AddMoreDepthEvents extends Command {
    protected $signature = 'dashboard:add-depth-events-2';
    protected $description = 'Add Newark, Minneapolis (Whipple) and Broadview depth protest-arrest events to the dashboard';

    /**
     * [title, url, source, date, lat, lng, location_label]. lat/lng/label are
     * null for additional sources covering an event already pinned above them.
     */
    private array $links = [
        // ── Newark NJ · Delaney Hall ICE jail · May 2026 (multi-night clashes) ──
        ['Protesters and police clash for days at the Delaney Hall ICE jail', 'https://www.cnn.com/2026/05/30/us/delaney-hall-new-jersey-ice-protests', 'CNN', '2026-05-29', 40.7180549, -74.1287016, 'Delaney Hall ICE detention center, Newark, NJ'],
        ['Six arrested as protesters clash with agents outside Delaney Hall', 'https://abc7ny.com/post/delaney-hall-protests-6-arrests-protesters-clash-ice-agents-outside-newark-nj/19192526/', 'ABC7 New York', '2026-05-29', null, null, null],

        // ── Minneapolis MN · Bishop Henry Whipple Federal Building · Jan 8, 2026 ──
        ['11 arrested at Whipple Federal Building protest after ICE shooting', 'https://www.fox9.com/news/minneapolis-ice-shooting-jan-8-2026', 'FOX 9', '2026-01-08', 44.8942120, -93.1948904, 'Bishop Henry Whipple Federal Building, Minneapolis (Fort Snelling), MN'],
        ['Anti-ICE protests outside Whipple Federal Building bring arrests', 'https://www.mprnews.org/story/2026/01/23/antiice-protests-outside-whipple-federal-building-brings-arrests', 'MPR News', '2026-01-08', null, null, null],

        // ── Broadview IL · Broadview ICE Processing Center · Nov 7, 2025 ("suburban moms" sit-in) ──
        ['14 "suburban moms" arrested in sit-in at Broadview ICE facility', 'https://chicago.suntimes.com/immigration/2025/11/07/fourteen-suburban-moms-arrested-in-sit-in-protest-outside-broadview-ice-facility', 'Chicago Sun-Times', '2025-11-07', 41.8681021, -87.8659406, 'Broadview ICE Processing Center, Broadview, IL'],
        ['Suburban moms arrested during sit-in at Broadview ICE facility', 'https://www.nbcchicago.com/news/local/suburban-moms-arrested-during-sit-in-at-ice-processing-facility-witnesses-say/3848961/', 'NBC Chicago', '2025-11-07', null, null, null],
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
