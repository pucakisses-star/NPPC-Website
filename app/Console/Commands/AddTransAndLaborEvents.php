<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Two non-immigration arrest events from February 2026, broadening the issue
 * mix again: the trans-rights blockade of HHS headquarters against a national
 * gender-affirming-care ban, and the civil-disobedience arrests during the
 * largest nurses' strike in New York City history. Each is pinned at its exact
 * venue.
 *
 * Same shape as the other add-*-events commands: every source URL becomes a
 * newswire item, one representative source per event carries the coordinates
 * (the marker), and rows are keyed by URL via updateOrCreate so the command is
 * idempotent. Manage them in /admin afterwards.
 *
 * Run on the server: php artisan dashboard:add-trans-nurses-events
 */
final class AddTransAndLaborEvents extends Command {
    protected $signature = 'dashboard:add-trans-nurses-events';
    protected $description = 'Add Feb 2026 trans-rights (HHS) and NYC nurses-strike arrest events to the dashboard';

    /**
     * [title, url, source, date, lat, lng, location_label]. lat/lng/label are
     * null for additional sources covering an event already pinned above them.
     */
    private array $links = [
        // ── Trans rights · HHS headquarters (Humphrey Building), Washington DC · Feb 17, 2026 ──
        ['24 arrested blockading HHS over trans youth care ban', 'https://www.washingtonblade.com/2026/02/20/trans-activists-arrested-outside-hhs-headquarters-in-d-c/', 'Washington Blade', '2026-02-17', 38.8866298, -77.0143854, 'HHS headquarters (Humphrey Building), Washington, DC'],
        ['25 arrested protesting HHS gender-affirming care rules', 'https://www.advocate.com/health/transgender-health/protest-anti-transgender-hhs-rules', 'The Advocate', '2026-02-17', null, null, null],
        ['Moms risk arrest to protect gender-affirming care', 'https://19thnews.org/2026/03/moms-arrested-gender-affirming-care/', 'The 19th', '2026-02-17', null, null, null],

        // ── Labor · Greater NY Hospital Association, 555 W 57th St, Manhattan · Feb 5, 2026 ──
        ['13 nurses arrested during NYC strike day of action', 'https://www.amny.com/news/nurses-strike-nyc-arrested-civil-disobedience/', 'amNewYork', '2026-02-05', 40.7703849, -73.9905079, 'Greater NY Hospital Association, 555 W 57th St, Manhattan, NY'],
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
