<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * The June 10, 2025 anti-ICE wave in the big Eastern cities, rounding out the
 * nationwide day already on the map (LA, SF, Seattle, Austin, Las Vegas,
 * Chicago, Denver): 86 arrested as thousands filled Foley Square in Lower
 * Manhattan, and 15 arrested blocking the Federal Detention Center in Center
 * City Philadelphia. Each is pinned at its exact venue.
 *
 * Same shape as the other add-*-events commands: every source URL becomes a
 * newswire item, one representative source per event carries the coordinates
 * (the marker), and rows are keyed by URL via updateOrCreate so the command is
 * idempotent. Manage them in /admin afterwards.
 *
 * Run on the server: php artisan dashboard:add-nyc-philly-events
 */
final class AddNycPhillyJune10Events extends Command {
    protected $signature = 'dashboard:add-nyc-philly-events';
    protected $description = 'Add the June 10, 2025 NYC (Foley Square) and Philadelphia anti-ICE protest arrests to the dashboard';

    /**
     * [title, url, source, date, lat, lng, location_label]. lat/lng/label are
     * null for additional sources covering an event already pinned above them.
     */
    private array $links = [
        // ── New York NY · Foley Square (Lower Manhattan) · Jun 10, 2025 ──
        ['Over 80 arrested as thousands flood Foley Square in anti-ICE protest', 'https://www.thecity.nyc/2025/06/10/ice-protests-arrests-nypd-trump-immigration/', 'THE CITY', '2025-06-10', 40.7144380, -74.0030793, 'Foley Square, Lower Manhattan, NY'],
        ['86 arrested at NYC anti-ICE protest in Foley Square', 'https://abc7ny.com/post/80-protesters-arrested-demonstrators-marched-lower-manhattan-amid-trump-immigration-crackdown/16720891/', 'ABC7 New York', '2025-06-10', null, null, null],

        // ── Philadelphia PA · Federal Detention Center (Center City) · Jun 10, 2025 ──
        ["15 arrested at anti-ICE protest in Philadelphia's Center City", 'https://whyy.org/articles/philadelphia-ice-protest-arrests-raids/', 'WHYY', '2025-06-10', 39.9528980, -75.1516234, 'Federal Detention Center, Center City, Philadelphia, PA'],
        ['Anti-ICE protest in Philadelphia leads to 15 arrests', 'https://www.cbsnews.com/philadelphia/news/ice-protest-philadelphia-donald-trump/', 'CBS Philadelphia', '2025-06-10', null, null, null],
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
