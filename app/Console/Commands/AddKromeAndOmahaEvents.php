<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Two more anti-ICE arrest events for the map: the 31 Sunrise Movement
 * protesters arrested blocking the gate of Miami's Krome Detention Center (a
 * Tampa photojournalist among them), and the protesters charged after trying to
 * stop ICE vehicles leaving the Glenn Valley Foods meatpacking raid in Omaha.
 * Each is pinned at its exact venue.
 *
 * Same shape as the other add-*-events commands: every source URL becomes a
 * newswire item, one representative source per event carries the coordinates
 * (the marker), and rows are keyed by URL via updateOrCreate so the command is
 * idempotent. Manage them in /admin afterwards.
 *
 * Run on the server: php artisan dashboard:add-krome-omaha-events
 */
final class AddKromeAndOmahaEvents extends Command {
    protected $signature = 'dashboard:add-krome-omaha-events';
    protected $description = 'Add the Miami Krome and Omaha Glenn Valley Foods ICE protest arrests to the dashboard';

    /**
     * [title, url, source, date, lat, lng, location_label]. lat/lng/label are
     * null for additional sources covering an event already pinned above them.
     */
    private array $links = [
        // ── Miami FL · Krome Detention Center · Nov 22, 2025 ──
        ["31 arrested blocking entrance to Miami's Krome Detention Center", 'https://www.nbcmiami.com/news/local/31-arrested-during-protest-at-krome-detention-center/3724939/', 'NBC 6 South Florida', '2025-11-22', 25.7534297, -80.4897094, 'Krome Detention Center, Miami, FL'],
        ['Tampa photojournalist arrested covering Miami ICE protest', 'https://www.tampabay.com/news/tampa/2025/11/26/dave-decker-arrest-miami-ice-protest-immigration-creative-loafing-zuma-press/', 'Tampa Bay Times', '2025-11-22', null, null, null],

        // ── Omaha NE · Glenn Valley Foods (68th & J St) · Jun 10, 2025 ──
        ['Four protesters charged after Omaha ICE raid at meatpacking plant', 'https://nebraskapublicmedia.org/en/news/news-articles/defendants-accused-of-interfering-with-law-enforcement-after-omaha-ice-raid-appear-in-federal-court/', 'Nebraska Public Media', '2025-06-10', 41.2149098, -96.0174295, 'Glenn Valley Foods, Omaha, NE'],
        ['Immigration raid rocks Nebraska plant; protesters and police clash', 'https://flatwaterfreepress.org/ice-raids-hit-omaha-meatpacking-plants/', 'Flatwater Free Press', '2025-06-10', null, null, null],
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
