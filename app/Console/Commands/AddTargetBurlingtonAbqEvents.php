<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Three more protest-arrest events for the map: the ~40 No ICE Philly activists
 * arrested in a sit-in inside a South Philadelphia Target, the 11 arrested in a
 * civil-disobedience action at the Burlington, Massachusetts ICE field office,
 * and the two detained at the Albuquerque ICE office (which puts New Mexico on
 * the map). Each is pinned at its exact venue.
 *
 * Same shape as the other add-*-events commands: every source URL becomes a
 * newswire item, one representative source per event carries the coordinates
 * (the marker), and rows are keyed by URL via updateOrCreate so the command is
 * idempotent. Manage them in /admin afterwards.
 *
 * Run on the server: php artisan dashboard:add-target-burlington-abq
 */
final class AddTargetBurlingtonAbqEvents extends Command {
    protected $signature = 'dashboard:add-target-burlington-abq';
    protected $description = 'Add the Philly Target, Burlington MA, and Albuquerque NM ICE protest arrests to the dashboard';

    /**
     * [title, url, source, date, lat, lng, location_label]. lat/lng/label are
     * null for additional sources covering an event already pinned above them.
     */
    private array $links = [
        // ── Philadelphia PA · Target, Mifflin St (South Philly) · Feb 5, 2026 ──
        ['About 40 anti-ICE activists arrested at sit-in inside South Philly Target', 'https://www.inquirer.com/news/philadelphia/south-philadelphia-target-protest-ice-arrests-20260205.html', 'The Philadelphia Inquirer', '2026-02-05', 39.9243865, -75.1461590, 'Target, Mifflin St, South Philadelphia, PA'],

        // ── Burlington MA · ICE field office, 1000 District Ave · Apr 29, 2026 ──
        ['11 arrested in civil disobedience at Burlington ICE facility', 'https://www.boston.com/news/local-news/2026/04/29/11-arrested-outside-burlington-ice-facility-in-act-of-civil-disobedience-police-say/', 'Boston.com', '2026-04-29', 42.4826346, -71.2088722, 'ICE field office, 1000 District Ave, Burlington, MA'],
        ['11 arrested after protesting outside Burlington ICE facility', 'https://www.wbur.org/news/2026/04/29/burlington-ice-detention-facility-arrests', 'WBUR', '2026-04-29', null, null, null],

        // ── Albuquerque NM · ICE office (Watson Dr SE) · Jan 9, 2026 ──
        ['Two detained at Albuquerque ICE office protest', 'https://sourcenm.com/2026/01/09/protestors-ice-clash-at-albuquerque-dhs-facility/', 'Source New Mexico', '2026-01-09', 35.0012708, -106.6170198, 'ICE office (Watson Dr SE), Albuquerque, NM'],
        ['Two arrested at Albuquerque ICE protest after confrontation', 'https://www.abqjournal.com/news/two-arrested-at-ice-protest-in-albuquerque/2957357', 'Albuquerque Journal', '2026-01-09', null, null, null],
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
