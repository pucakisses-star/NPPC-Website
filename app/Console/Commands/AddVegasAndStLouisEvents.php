<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Two more protest-arrest events on the map, in states not previously covered:
 * the mass arrest at the June 2025 anti-ICE march in downtown Las Vegas, and
 * the arrests of activists who disrupted the St. Louis mayor's State of the
 * City address demanding tornado-recovery funding for north St. Louis.
 *
 * Same shape as the other add-*-events commands: every source URL becomes a
 * newswire item, one representative source per event carries the coordinates
 * (the marker), and rows are keyed by URL via updateOrCreate so the command is
 * idempotent. Manage them in /admin afterwards.
 *
 * Run on the server: php artisan dashboard:add-vegas-stlouis-events
 */
final class AddVegasAndStLouisEvents extends Command {
    protected $signature = 'dashboard:add-vegas-stlouis-events';
    protected $description = 'Add Las Vegas anti-ICE and St. Louis State-of-the-City arrest events to the dashboard';

    /**
     * [title, url, source, date, lat, lng, location_label]. lat/lng/label are
     * null for additional sources covering an event already pinned above them.
     */
    private array $links = [
        // ── Las Vegas NV · downtown (Lloyd D. George Federal Courthouse) · Jun 11, 2025 ──
        ['Nearly 100 arrested at downtown Las Vegas anti-ICE protest', 'https://www.reviewjournal.com/crime/nearly-100-arrested-in-downtown-las-vegas-ice-protest-police-say-3384453/', 'Las Vegas Review-Journal', '2025-06-11', 36.1661179, -115.1426318, 'Downtown Las Vegas (federal courthouse), NV'],
        ['Anti-ICE protest in downtown Las Vegas turns into standoff', 'https://lasvegassun.com/news/2025/jun/12/anti-ice-protest-in-downtown-las-vegas-turns-into/', 'Las Vegas Sun', '2025-06-11', null, null, null],

        // ── St. Louis MO · St. Louis City Hall · Apr 17, 2026 (State of the City) ──
        ["Five arrested disrupting St. Louis mayor's State of the City", 'https://www.stlpr.org/government-politics-issues/2026-04-17/st-louis-mayor-cara-spencer-speech-protestors-arrested', 'St. Louis Public Radio', '2026-04-17', 38.6268322, -90.1994026, 'St. Louis City Hall, MO'],
        ['St. Louis police defend arrests of north-city protesters', 'https://www.ksdk.com/article/news/local/state-of-city-chaos-st-louis-police-defend-arrests-of-north-city-protestors/63-c21bfa44-83e8-4ad7-9d67-da50478f04b2', 'KSDK', '2026-04-17', null, null, null],
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
