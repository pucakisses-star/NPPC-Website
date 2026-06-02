<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Depth additions to the June 2025 anti-ICE map plus a new state: three
 * arrested at the Tucson ICE office (Arizona's first marker), six arrested
 * along Buford Highway in Brookhaven (metro Atlanta), one arrested at the
 * Margaret Hunt Hill Bridge march in Dallas, and the October 2025 tear-gassed
 * protest at the Portland ICE facility. Each is pinned at its exact venue.
 *
 * Same shape as the other add-*-events commands: every source URL becomes a
 * newswire item, one representative source per event carries the coordinates
 * (the marker), and rows are keyed by URL via updateOrCreate so the command is
 * idempotent. Manage them in /admin afterwards.
 *
 * Run on the server: php artisan dashboard:add-depth-events
 */
final class AddDepthArrestEvents extends Command {
    protected $signature = 'dashboard:add-depth-events';
    protected $description = 'Add Tucson AZ plus Atlanta, Dallas and Portland depth protest-arrest events to the dashboard';

    /**
     * [title, url, source, date, lat, lng, location_label]. lat/lng/label are
     * null for additional sources covering an event already pinned above them.
     */
    private array $links = [
        // ── Tucson AZ · ICE office (S Country Club Rd) · Jun 11, 2025 ──
        ['Three arrested at anti-ICE protest outside Tucson ICE office', 'https://ktar.com/immigration/ice-protest-in-tucson/5716880/', 'KTAR News', '2025-06-11', 32.1353443, -110.9260530, 'ICE office (S Country Club Rd), Tucson, AZ'],
        ['Three arrested after anti-ICE protest in Tucson turns tense', 'https://www.azfamily.com/2025/06/12/watch-anti-ice-protest-tucson-turns-violent/', 'AZFamily', '2025-06-11', null, null, null],

        // ── Brookhaven / metro Atlanta GA · Buford Highway · Jun 10, 2025 ──
        ['6 arrested at anti-ICE protest along Buford Highway in Brookhaven', 'https://www.ajc.com/news/2025/06/immigration-protest-along-buford-highway-marred-by-tear-gas-and-fireworks/', 'The Atlanta Journal-Constitution', '2025-06-10', 33.8521948, -84.3185412, 'Buford Highway, Brookhaven (Atlanta), GA'],
        ['Brookhaven police identify 6 arrested at Buford Highway anti-ICE protest', 'https://www.atlantanewsfirst.com/2025/06/11/brookhaven-police-identify-suspects-arrested-during-anti-ice-protest-buford-highway/', 'Atlanta News First', '2025-06-10', null, null, null],

        // ── Dallas TX · Margaret Hunt Hill Bridge · Jun 9, 2025 ──
        ["One arrested at anti-ICE march on Dallas' Margaret Hunt Hill Bridge", 'https://www.cbsnews.com/texas/news/arrest-during-ice-protest-margaret-hunt-hill-bridge-dallas/', 'CBS Texas', '2025-06-09', 32.7800134, -96.8220017, 'Margaret Hunt Hill Bridge, Dallas, TX'],

        // ── Portland OR · ICE facility, South Waterfront · Oct 4, 2025 ──
        ['Federal officers fire tear gas, arrest several at Portland ICE facility', 'https://www.opb.org/article/2025/10/04/portland-ice-facility-protest/', 'OPB', '2025-10-04', 45.4925394, -122.6725455, 'ICE facility, South Waterfront, Portland, OR'],
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
