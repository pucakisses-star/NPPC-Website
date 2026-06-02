<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Two more protest-arrest events: the nine arrested staging a sit-in at Senator
 * Susan Collins' office in Portland, Maine (which puts Maine on the map), and
 * the 17 arrested at the Colorado State Capitol during the June 10, 2025
 * anti-ICE wave (a separate venue from the Denver Civic Center "No Kings"
 * marker). Each is pinned at its exact venue.
 *
 * Same shape as the other add-*-events commands: every source URL becomes a
 * newswire item, one representative source per event carries the coordinates
 * (the marker), and rows are keyed by URL via updateOrCreate so the command is
 * idempotent. Manage them in /admin afterwards.
 *
 * Run on the server: php artisan dashboard:add-maine-denver-events
 */
final class AddPortlandMaineDenverEvents extends Command {
    protected $signature = 'dashboard:add-maine-denver-events';
    protected $description = 'Add the Portland ME (Sen. Collins office) and Denver Capitol anti-ICE arrests to the dashboard';

    /**
     * [title, url, source, date, lat, lng, location_label]. lat/lng/label are
     * null for additional sources covering an event already pinned above them.
     */
    private array $links = [
        // ── Portland ME · Sen. Susan Collins' office (Canal Plaza) · Jan 28, 2026 ──
        ["Nine anti-ICE protesters arrested at Sen. Collins' office in Portland, Maine", 'https://www.cbsnews.com/boston/news/susan-collins-ice-protest-portland-maine/', 'CBS Boston', '2026-01-28', 43.6570957, -70.2556842, "Sen. Susan Collins' office (Canal Plaza), Portland, ME"],

        // ── Denver CO · Colorado State Capitol · Jun 10, 2025 ──
        ['17 arrested at anti-ICE protest at the Colorado Capitol', 'https://www.coloradopolitics.com/colorado-in-dc/denver-protest-arrests-state-capitol/article_727b84fd-a17f-5c68-b716-ea952541141e.html', 'Colorado Politics', '2025-06-10', 39.7399969, -104.9844034, 'Colorado State Capitol, Denver, CO'],
        ['Anti-ICE demonstrations expand to Colorado; police arrest 17', 'https://www.axios.com/local/denver/2025/06/11/ice-protests-colorado-los-angeles', 'Axios Denver', '2025-06-10', null, null, null],
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
