<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the wave of 2025–26 arrests at AI data-center hearings / ICE protests
 * to the dashboard newswire, each plotted on the map at its exact venue — the
 * council chamber, university, statehouse, business park, etc. — rather than a
 * vague town centre.
 *
 * Every source URL becomes a newswire item. To keep the map readable, ONE
 * representative source per event carries the coordinates (the marker); the
 * other sources for the same event are feed-only (no lat/lng). Rows are keyed
 * by URL via updateOrCreate, so the command is idempotent and safe to re-run.
 *
 * Manage them afterwards in /admin (Dashboard Links).
 *
 * Run on the server: php artisan dashboard:add-arrest-events
 */
final class AddDataCenterArrestEvents extends Command {
    protected $signature = 'dashboard:add-arrest-events';
    protected $description = 'Add recent data-center / ICE protest arrest events to the dashboard newswire and map';

    /**
     * [title, url, source, date, lat, lng, location_label]. lat/lng/label are
     * null for additional sources covering an event already pinned above them.
     */
    private array $links = [
        // ── Claremore, OK · Rogers State University · Feb 17, 2026 (Project Mustang) ──
        ['Arrest made at heated Claremore data center meeting', 'https://www.newson6.com/tulsa-oklahoma-news/arrest-made-during-heated-claremore-meeting-over-proposed-data-center', 'News On 6', '2026-02-17', 36.3186099, -95.6361137, 'Rogers State University, Claremore, OK'],
        ['Community activist arrested at Claremore data center meeting', 'https://ktul.com/news/local/community-activist-arrested-at-data-center-meeting-in-claremore', 'KTUL', '2026-02-17', null, null, null],
        ['Oklahoma man arrested at AI data center meeting', 'https://www.businessinsider.com/data-center-meeting-claremore-oklahoma-man-arrested-beale-infrastructure-ai-2026-2', 'Business Insider', '2026-02-17', null, null, null],
        ['Oklahoma farmer jailed for trespassing at AI data center town hall', 'https://www.tomshardware.com/tech-industry/big-tech/oklahoma-farmer-arrested-and-jailed-for-trespassing-during-ai-data-center-town-hall-removed-by-officers-after-going-a-few-seconds-over-allotted-speaking-time-trying-to-hand-paperwork-to-counselors', "Tom's Hardware", '2026-02-17', null, null, null],

        // ── Port Washington, WI · Common Council, City Hall · Dec 2, 2025 ──
        ['Three arrested at Port Washington data center hearing', 'https://www.datacenterdynamics.com/en/news/three-arrested-at-data-center-hearing-in-port-washington-wisconsin/', 'DataCenter Dynamics', '2025-12-02', 43.3876540, -87.8710410, 'Port Washington City Hall, WI'],
        ['Arrests at Port Washington data center meeting', 'https://www.fox6now.com/news/port-washington-data-center-meeting-arrests', 'FOX6 Milwaukee', '2025-12-02', null, null, null],
        ['Three arrested at Port Washington city data center meeting', 'https://www.fox6now.com/news/port-washington-data-center-concerns-arrests-city-meeting', 'FOX6 Milwaukee', '2025-12-03', null, null, null],
        ['Arrest at Port Washington data center protest', 'https://spectrumnews1.com/wi/milwaukee/news/2025/12/07/port-washington-data-center-peaceful-protest-arrest', 'Spectrum News 1', '2025-12-02', null, null, null],
        ['Woman dragged from council meeting over data center protest', 'https://www.alternet.org/woman-violently-arrested-after-speaking-out-against-ai-data-centers/', 'AlterNet', '2025-12-02', null, null, null],

        // ── Columbus, OH · Ohio Statehouse · Jun 1, 2026 ──
        ["Blogger 'The Rooster' arrested outside Ohio Statehouse", 'https://signalohio.org/progressive-blogger-the-rooster-arrested-outside-statehouse-charged-with-harassment/', 'Signal Ohio', '2026-06-01', 39.9611755, -82.9987942, 'Ohio Statehouse, Columbus, OH'],

        // ── Dixon, IL · threats over a data center near Rock Falls · May 27, 2026 ──
        ['Illinois man arrested after threats over data center', 'https://www.datacenterdynamics.com/en/news/illinois-man-arrested-after-threatening-local-authorities-to-stop-data-center-development/', 'DataCenter Dynamics', '2026-05-27', 41.8426378, -89.4832453, 'Dixon, IL (Rock Falls data center dispute)'],
        ['Dixon data center critic arrested', 'https://www.businessinsider.com/dixon-illinois-data-center-development-critic-arrested-2026-5', 'Business Insider', '2026-05-27', null, null, null],
        ['Dixon man arrested over Rock Falls data center threats', 'https://hoodline.com/2026/05/dixon-man-busted-after-threats-over-rock-falls-data-center-site/', 'Hoodline', '2026-05-27', null, null, null],

        // ── Williston, VT · White Cap Business Park (ICE targeting center) · Feb 9, 2026 ──
        ['11 arrested during ICE protest at Williston business park', 'https://vtdigger.org/2026/02/10/11-arrested-during-ice-protest-at-williston-business-park/', 'VTDigger', '2026-02-09', 44.4607481, -73.1229347, 'White Cap Business Park, Williston, VT'],
        ['Arrests at Williston ICE surveillance center protest', 'https://vnews.com/2026/02/11/williston-ice-protest-arrests/', 'Valley News', '2026-02-09', null, null, null],

        // ── El Centro, CA · Imperial County Board of Supervisors · Apr 11, 2026 ──
        ['Man arrested at Imperial County data center board meeting', 'https://www.latimes.com/california/story/2026-04-11/man-speaking-against-data-center-arrested-at-imperial-county-board-meeting-as-tensions-flare-nationwide', 'Los Angeles Times', '2026-04-11', 32.7929238, -115.5631508, 'Imperial County Admin Center, El Centro, CA'],

        // ── El Centro, CA · near El Centro Library · Apr 20, 2026 ──
        ['El Centro resident arrested for online threats over data center', 'https://www.kpbs.org/news/public-safety/2026/04/20/el-centro-resident-arrested-for-allegedly-making-online-threats-against-data-center-developer', 'KPBS', '2026-04-20', 32.7917070, -115.5556662, 'El Centro, CA (near El Centro Library)'],

        // ── Andover Township, NJ · "The Barn", 146 Lake Iliff Rd · May 7, 2026 ──
        ['NJ man arrested at town meeting over secret data center deal', 'https://thenerdstash.com/new-jersey-man-arrested-at-town-meeting-after-confronting-officials-over-secret-ai-data-center-deal-how-much-are-they-paying-you/', 'The Nerd Stash', '2026-05-07', 41.0312573, -74.7207799, 'The Barn, Andover Township, NJ'],

        // ── Hobart, IN · Police Court Complex, 705 E 4th St · May 7, 2026 ──
        ['Hobart data center meeting draws large crowd, one arrest', 'https://www.chicagotribune.com/2026/05/09/hobart-meeting-on-data-centers-brings-large-crowd-tight-security-and-one-arrest/', 'Chicago Tribune', '2026-05-07', 41.5316011, -87.2525158, 'Hobart Police Court Complex, IN'],
        ['Man removed and arrested at Indiana data center meeting', 'https://www.fox32chicago.com/news/video-shows-man-removed-arrested-indiana-data-center-meeting', 'FOX 32 Chicago', '2026-05-07', null, null, null],
        ['Video: man removed from Indiana data center meeting', 'https://www.fox32chicago.com/video/fmc-qtdelt547oamiybt', 'FOX 32 Chicago', '2026-05-07', null, null, null],

        // ── Philadelphia, PA · Delaware Valley Intelligence Center · Jun 1, 2026 ──
        ['Police tracked anti-data-center speech as extremism', 'https://theintercept.com/2026/06/01/ai-data-center-protest-police-surveillance/', 'The Intercept', '2026-06-01', 39.9105823, -75.2219422, 'Delaware Valley Intelligence Center, Philadelphia, PA'],
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
