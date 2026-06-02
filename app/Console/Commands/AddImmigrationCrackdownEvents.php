<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Arrests of elected officials and clergy who tried to resist or observe the
 * 2025–26 immigration crackdown — a distinct strand of the same story, where
 * the people detained were a mayor, a city comptroller and faith leaders. Each
 * is pinned at its exact venue (the ICE detention center, the immigration
 * court, the ICE processing center).
 *
 * Same shape as the other add-*-events commands: every source URL becomes a
 * newswire item, one representative source per event carries the coordinates
 * (the marker), and rows are keyed by URL via updateOrCreate so the command is
 * idempotent. Manage them in /admin afterwards.
 *
 * Run on the server: php artisan dashboard:add-immigration-crackdown-events
 */
final class AddImmigrationCrackdownEvents extends Command {
    protected $signature = 'dashboard:add-immigration-crackdown-events';
    protected $description = 'Add immigration-crackdown resistance arrests (officials, clergy) to the dashboard';

    /**
     * [title, url, source, date, lat, lng, location_label]. lat/lng/label are
     * null for additional sources covering an event already pinned above them.
     */
    private array $links = [
        // ── Newark NJ · Delaney Hall ICE detention center · May 9, 2025 (Mayor Ras Baraka) ──
        ['Newark Mayor Ras Baraka arrested at Delaney Hall ICE facility', 'https://www.cbsnews.com/newyork/news/newark-mayor-ras-baraka-ice-arrest/', 'CBS New York', '2025-05-09', 40.7180549, -74.1287016, 'Delaney Hall ICE detention center, Newark, NJ'],
        ['Newark mayor charged with trespassing after ICE-facility arrest', 'https://www.washingtonpost.com/nation/2025/05/09/newark-mayor-ice-arrest-ras-baraka-nj/', 'The Washington Post', '2025-05-09', null, null, null],

        // ── Manhattan NY · 26 Federal Plaza immigration court · Jun 17, 2025 (Comptroller Brad Lander) ──
        ['NYC Comptroller Brad Lander arrested by ICE at immigration court', 'https://www.cnn.com/2025/06/17/us/brad-lander-ice-arrest-nyc', 'CNN', '2025-06-17', 40.7154682, -74.0042025, '26 Federal Plaza immigration court, Manhattan, NY'],
        ['Brad Lander detained by masked federal agents, released without charges', 'https://www.thecity.nyc/2025/06/17/brad-lander-arrest-ice-immigration-court/', 'THE CITY', '2025-06-17', null, null, null],

        // ── Broadview IL · Broadview ICE Processing Center · Nov 14, 2025 (clergy) ──
        ['At least seven faith leaders arrested at Broadview ICE facility', 'https://religionnews.com/2025/11/15/at-least-seven-faith-leaders-arrested-at-ice-facility-protest/', 'Religion News Service', '2025-11-14', 41.8681021, -87.8659406, 'Broadview ICE Processing Center, Broadview, IL'],
        ['Evanston residents among 21 arrested in Broadview ICE protest', 'https://evanstonroundtable.com/2025/11/14/evanston-residents-among-21-arrested-in-ice-protest/', 'Evanston RoundTable', '2025-11-14', null, null, null],
        ['Pastors describe brutality of arrests at Broadview ICE facility', 'https://chicago.suntimes.com/immigration/2025/11/16/clergy-arrests-broadview-ice', 'Chicago Sun-Times', '2025-11-14', null, null, null],
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
