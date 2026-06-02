<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * The June 2025 anti-ICE protest wave as it spread across the country — Austin,
 * Seattle and metro Atlanta — extending the earlier LA / No Kings batch. Each
 * is pinned at its exact venue (the federal buildings the marches reached, and
 * the DeKalb County road where journalist Mario Guevara was arrested while
 * livestreaming, a case that ended in his deportation).
 *
 * Same shape as the other add-*-events commands: every source URL becomes a
 * newswire item, one representative source per event carries the coordinates
 * (the marker), and rows are keyed by URL via updateOrCreate so the command is
 * idempotent. Manage them in /admin afterwards.
 *
 * Run on the server: php artisan dashboard:add-june2025-wave-events
 */
final class AddJune2025WaveEvents extends Command {
    protected $signature = 'dashboard:add-june2025-wave-events';
    protected $description = 'Add June 2025 anti-ICE protest arrests (Austin, Seattle, Atlanta/DeKalb) to the dashboard';

    /**
     * [title, url, source, date, lat, lng, location_label]. lat/lng/label are
     * null for additional sources covering an event already pinned above them.
     */
    private array $links = [
        // ── Austin TX · J.J. Pickle Federal Building · Jun 9, 2025 ──
        ['At least 13 arrested after anti-ICE march in Austin', 'https://www.kxan.com/news/local/austin/austin-police-holds-press-conference-following-anti-ice-protest/', 'KXAN', '2025-06-09', 30.2694066, -97.7390923, 'J.J. Pickle Federal Building, Austin, TX'],
        ['Austin anti-ICE protest met with tear gas, multiple arrested', 'https://www.fox7austin.com/news/austin-ice-protest-arrests-police-livestream', 'FOX 7 Austin', '2025-06-09', null, null, null],

        // ── Seattle WA · Henry M. Jackson Federal Building · Jun 11, 2025 ──
        ['Eight arrested at anti-ICE protest outside Seattle federal building', 'https://www.king5.com/article/news/local/protest-blocks-intersections-downtown-seattle-ice/281-5eb88df3-e1cf-4d92-811d-82a901b3cdab', 'KING 5', '2025-06-11', 47.6045821, -122.3354856, 'Henry M. Jackson Federal Building, Seattle, WA'],
        ['Several arrested during Seattle anti-ICE protest', 'https://www.fox13seattle.com/news/arrests-seattle-anti-ice-protest', 'FOX 13 Seattle', '2025-06-11', null, null, null],

        // ── DeKalb County GA · Chamblee Tucker Rd (Embry Village) · Jun 14, 2025 (journalist Mario Guevara) ──
        ['Salvadoran journalist Mario Guevara arrested covering DeKalb protest', 'https://www.11alive.com/article/news/local/protests/bodycam-video-salvadoran-journalist-arrested-dekalb-county-mario-guevara/85-8de24d09-dfb6-4546-be0e-7d1bb9e393f3', '11Alive', '2025-06-14', 33.8854949, -84.2846477, 'Chamblee Tucker Rd (Embry Village), DeKalb County, GA'],
        ['DeKalb police hand journalist Mario Guevara over to ICE', 'https://atlantaciviccircle.org/2025/06/18/dekalb-police-journalist-mario-guevara-ice-custody/', 'Atlanta Civic Circle', '2025-06-14', null, null, null],
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
