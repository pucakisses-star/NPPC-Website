<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * The June 2025 protest wave for the dashboard: the Los Angeles anti-ICE
 * uprising (including the felony charge against SEIU California president
 * David Huerta) and the nationwide "No Kings" day on June 14 — arrests in
 * Spokane, Los Angeles and Denver — each pinned at its exact venue.
 *
 * Same shape as the other add-*-events commands: every source URL becomes a
 * newswire item, one representative source per event carries the coordinates
 * (the marker), and rows are keyed by URL via updateOrCreate so the command is
 * idempotent. Manage them in /admin afterwards.
 *
 * Run on the server: php artisan dashboard:add-june2025-events
 */
final class AddJune2025ProtestEvents extends Command {
    protected $signature = 'dashboard:add-june2025-events';
    protected $description = 'Add June 2025 protest-arrest events (LA anti-ICE, David Huerta, nationwide No Kings day) to the dashboard';

    /**
     * [title, url, source, date, lat, lng, location_label]. lat/lng/label are
     * null for additional sources covering an event already pinned above them.
     */
    private array $links = [
        // ── Immigration / labor · Edward R. Roybal Federal Building, Los Angeles · Jun 6, 2025 ──
        ['SEIU leader David Huerta charged with felony after ICE-raid arrest', 'https://www.cbsnews.com/news/david-huerta-seiu-charged-los-angeles-ice-protest-trump/', 'CBS News', '2025-06-06', 34.0528364, -118.2389802, 'Edward R. Roybal Federal Building, Los Angeles, CA'],
        ['SEIU leader David Huerta released after charge for impeding ICE', 'https://laist.com/news/la-immigration-raids-protests-huerta-charged', 'LAist', '2025-06-06', null, null, null],

        // ── Immigration · Federal Building, 300 N Los Angeles St, Los Angeles · Jun 10, 2025 ──
        ['50 arrested at anti-ICE protest outside downtown LA federal building', 'https://abc7.com/live-updates/live-updates-protesters-clash-officers-during-ice-protest-downtown-la/18511419/', 'ABC7 Los Angeles', '2025-06-10', 34.0537473, -118.2396971, 'Federal Building, 300 N Los Angeles St, Los Angeles, CA'],
        ['At least 71 charged after Los Angeles anti-ICE protests', 'https://lapublicpress.org/2025/08/ice-raids-la-arrests-charges/', 'LA Public Press', '2025-06-10', null, null, null],

        // ── "No Kings" day · Spokane City Hall, WA · Jun 14, 2025 ──
        ['11 arrested at Spokane No Kings protest outside City Hall', 'https://www.spokesman.com/stories/2025/jun/15/11-arrested-at-spokanes-no-kings-protest/', 'The Spokesman-Review', '2025-06-14', 47.6605613, -117.4241938, 'Spokane City Hall, WA'],

        // ── "No Kings" day · Los Angeles City Hall · Jun 14, 2025 ──
        ['38 arrested after No Kings protest in downtown Los Angeles', 'https://www.cbsnews.com/losangeles/news/38-people-arrested-following-no-kings-protest-in-downtown-la/', 'CBS Los Angeles', '2025-06-14', 34.0536961, -118.2429212, 'Los Angeles City Hall'],

        // ── "No Kings" day · Civic Center, Denver CO · Jun 14, 2025 ──
        ['36 arrested after Denver No Kings demonstration', 'https://coloradosun.com/2025/06/15/no-kings-arrests-denver/', 'The Colorado Sun', '2025-06-14', 39.7392357, -104.9891142, 'Civic Center, Denver, CO'],
        ['Denver police arrest dozens after No Kings protest', 'https://www.cbsnews.com/colorado/news/denver-police-arrest-17-people-in-connection-with-no-kings-protest-department-says/', 'CBS Colorado', '2025-06-14', null, null, null],
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
