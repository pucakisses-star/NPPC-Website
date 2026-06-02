<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Two depth additions in cities already on the map: the 92 arrested on the
 * second night of the San Francisco anti-ICE protests (at Market & Van Ness,
 * a different spot from the June 8 Sansome St marker), and the Los Angeles
 * student walkout against ICE operations in February 2026. Each is pinned at
 * its venue.
 *
 * Same shape as the other add-*-events commands: every source URL becomes a
 * newswire item, one representative source per event carries the coordinates
 * (the marker), and rows are keyed by URL via updateOrCreate so the command is
 * idempotent. Manage them in /admin afterwards.
 *
 * Run on the server: php artisan dashboard:add-sf-la-depth
 */
final class AddSfLaDepthEvents extends Command {
    protected $signature = 'dashboard:add-sf-la-depth';
    protected $description = 'Add San Francisco (June 9) and LA student-walkout depth arrest events to the dashboard';

    /**
     * [title, url, source, date, lat, lng, location_label]. lat/lng/label are
     * null for additional sources covering an event already pinned above them.
     */
    private array $links = [
        // ── San Francisco CA · Market & Van Ness · Jun 9, 2025 (second night) ──
        ['92 arrested on second night of San Francisco anti-ICE protests', 'https://www.kqed.org/news/12043544/dozens-more-arrested-in-calmer-night-of-san-francisco-ice-protests', 'KQED', '2025-06-09', 37.7753971, -122.4193700, 'Market & Van Ness, San Francisco, CA'],
        ['SFPD arrests dozens on second night of mass ICE protests', 'https://missionlocal.org/2025/06/sf-mission-march-mobilized-thousands-against-ice/', 'Mission Local', '2025-06-09', null, null, null],

        // ── Los Angeles CA · City Hall (student walkout) · Feb 4, 2026 ──
        ['Several arrested as LA students walk out against ICE operations', 'https://www.cbsnews.com/losangeles/news/lapd-arrests-protesters-students-gathered-downtown-los-angeles-ice-operations-immigration/', 'CBS Los Angeles', '2026-02-04', 34.0536961, -118.2429212, 'Los Angeles City Hall (student walkout), CA'],
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
