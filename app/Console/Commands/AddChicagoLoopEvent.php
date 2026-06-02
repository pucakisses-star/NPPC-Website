<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * The June 2025 downtown Chicago anti-ICE protest: thousands rallied at Federal
 * Plaza and marched through the Loop in solidarity with Los Angeles, and 17
 * people were arrested (four charged with felonies). A separate marker from the
 * Broadview ICE-facility events already on the map, pinned at Federal Plaza.
 *
 * Same shape as the other add-*-events commands: every source URL becomes a
 * newswire item, one representative source per event carries the coordinates
 * (the marker), and rows are keyed by URL via updateOrCreate so the command is
 * idempotent. Manage them in /admin afterwards.
 *
 * Run on the server: php artisan dashboard:add-chicago-loop-event
 */
final class AddChicagoLoopEvent extends Command {
    protected $signature = 'dashboard:add-chicago-loop-event';
    protected $description = 'Add the downtown Chicago (Federal Plaza) anti-ICE protest arrests to the dashboard';

    /**
     * [title, url, source, date, lat, lng, location_label]. lat/lng/label are
     * null for additional sources covering an event already pinned above them.
     */
    private array $links = [
        // ── Chicago IL · Federal Plaza (the Loop) · Jun 10, 2025 ──
        ['17 arrested as thousands rally against ICE in downtown Chicago', 'https://www.wbez.org/crime/2025/06/11/17-arrested-4-charged-with-felonies-as-thousands-gathered-for-anti-ice-protests-in-downtown-chicago', 'WBEZ', '2025-06-10', 41.8791718, -87.6292686, 'Federal Plaza, downtown Chicago, IL'],
        ['17 arrested, 4 charged with felonies at anti-ICE protest in the Loop', 'https://chicago.suntimes.com/crime/2025/06/11/17-arrested-4-charged-with-felonies-as-thousands-gathered-for-anti-ice-protests-in-downtown-chicago', 'Chicago Sun-Times', '2025-06-10', null, null, null],
        ['17 arrested at anti-ICE protest in downtown Chicago', 'https://www.cbsnews.com/chicago/news/17-arrested-chicago-ice-protest-downtown-police/', 'CBS Chicago', '2025-06-10', null, null, null],
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
