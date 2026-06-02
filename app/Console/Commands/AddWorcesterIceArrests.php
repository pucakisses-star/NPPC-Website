<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * The May 2025 Worcester, Massachusetts ICE confrontation: as federal agents
 * detained a mother on Eureka Street, dozens of neighbors surrounded them and
 * Worcester police arrested two residents — including a School Committee
 * candidate — in a clash that put the city on edge for days. Pinned at the
 * Eureka Street scene.
 *
 * Same shape as the other add-*-events commands: every source URL becomes a
 * newswire item, one representative source per event carries the coordinates
 * (the marker), and rows are keyed by URL via updateOrCreate so the command is
 * idempotent. Manage them in /admin afterwards.
 *
 * Run on the server: php artisan dashboard:add-worcester-event
 */
final class AddWorcesterIceArrests extends Command {
    protected $signature = 'dashboard:add-worcester-event';
    protected $description = 'Add the Worcester MA Eureka Street ICE-confrontation arrests to the dashboard';

    /**
     * [title, url, source, date, lat, lng, location_label]. lat/lng/label are
     * null for additional sources covering an event already pinned above them.
     */
    private array $links = [
        // ── Worcester MA · Eureka Street · May 8, 2025 ──
        ['Two arrested as Worcester neighbors confront ICE detention', 'https://www.bostonglobe.com/2025/05/08/metro/ice-arrests-worcester-woman-spurs-protest/', 'The Boston Globe', '2025-05-08', 42.2389790, -71.8491721, 'Eureka Street, Worcester, MA'],
        ['Two arrested after neighbors try to stop ICE detaining Worcester mother', 'https://www.boston.com/news/local-news/2025/05/08/two-arrested-after-neighbors-try-to-stop-ice-agents-from-detaining-worcester-mother/', 'Boston.com', '2025-05-08', null, null, null],
        ['Worcester ICE raid has city on edge', 'https://www.wbur.org/news/2025/05/16/worcester-police-ice-arrest-protesters-activists', 'WBUR', '2025-05-08', null, null, null],
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
