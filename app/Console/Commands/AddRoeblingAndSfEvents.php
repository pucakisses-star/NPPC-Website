<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Two more anti-ICE protest-arrest events for the map: the mass arrest at the
 * June 2025 protest outside San Francisco's ICE field office, and the dozen-plus
 * arrests at the July 2025 march across the John A. Roebling Suspension Bridge
 * (Cincinnati / Covington) over the detention of Imam Ayman Soliman. Each is
 * pinned at its exact venue.
 *
 * Same shape as the other add-*-events commands: every source URL becomes a
 * newswire item, one representative source per event carries the coordinates
 * (the marker), and rows are keyed by URL via updateOrCreate so the command is
 * idempotent. Manage them in /admin afterwards.
 *
 * Run on the server: php artisan dashboard:add-roebling-sf-events
 */
final class AddRoeblingAndSfEvents extends Command {
    protected $signature = 'dashboard:add-roebling-sf-events';
    protected $description = 'Add the San Francisco and Roebling Bridge (Cincinnati/Covington) anti-ICE arrest events to the dashboard';

    /**
     * [title, url, source, date, lat, lng, location_label]. lat/lng/label are
     * null for additional sources covering an event already pinned above them.
     */
    private array $links = [
        // ── San Francisco CA · ICE field office, 630 Sansome St · Jun 8, 2025 ──
        ['Over 150 arrested at San Francisco anti-ICE protest', 'https://www.kqed.org/news/12043255/sf-protesters-denounce-ice-raids-and-trumps-national-guard-deployment-to-la', 'KQED', '2025-06-08', 37.7960291, -122.4016621, 'ICE field office, 630 Sansome St, San Francisco, CA'],
        ['ICE protest in San Francisco ends with 154 arrested', 'https://sfstandard.com/2025/06/08/anti-ice-protest-s/', 'The San Francisco Standard', '2025-06-08', null, null, null],

        // ── Cincinnati / Covington · John A. Roebling Suspension Bridge · Jul 17, 2025 ──
        ['Police arrest more than a dozen at anti-ICE Roebling Bridge march', 'https://www.wvxu.org/local-news/2025-07-18/covington-police-arrest-at-anti-ice-march-across-roebling-bridge', 'WVXU', '2025-07-17', 39.0928989, -84.5098665, 'John A. Roebling Suspension Bridge, Covington, KY'],
        ['UC students detained during Roebling Bridge ICE protest', 'https://www.newsrecord.org/news/uc-students-detained-during-roebling-bridge-ice-protest/article_4f71371c-9a6a-4705-9bda-c34012acdef5.html', 'The News Record', '2025-07-17', null, null, null],
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
