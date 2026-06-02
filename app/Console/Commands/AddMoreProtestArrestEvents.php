<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * A further batch of recent protest-arrest events for the dashboard, widening
 * the issue spread again — disability rights / Medicaid cuts, labor strikes,
 * voting rights / redistricting and press freedom — each pinned at its exact
 * venue (Senate and House office buildings, the Empire State Building, a
 * roasting plant, state capitols, a named church).
 *
 * Same shape as the earlier add-*-events commands: every source URL becomes a
 * newswire item, one representative source per event carries the coordinates
 * (the marker), and rows are keyed by URL via updateOrCreate so the command is
 * idempotent. Manage them in /admin afterwards.
 *
 * Run on the server: php artisan dashboard:add-more-protest-events
 */
final class AddMoreProtestArrestEvents extends Command {
    protected $signature = 'dashboard:add-more-protest-events';
    protected $description = 'Add more protest-arrest events (disability/Medicaid, labor, voting rights, press freedom) to the dashboard';

    /**
     * [title, url, source, date, lat, lng, location_label]. lat/lng/label are
     * null for additional sources covering an event already pinned above them.
     */
    private array $links = [
        // ── Disability rights / Medicaid · Russell Senate Office Building, DC · Jun 25, 2025 ──
        ['Dozens arrested protesting Medicaid cuts at Senate building', 'https://www.nbcwashington.com/news/politics/more-than-30-arrested-at-senate-building-while-protesting-medicaid-cuts/3944587/', 'NBC4 Washington', '2025-06-25', 38.8928229, -77.0062654, 'Russell Senate Office Building, Washington, DC'],
        ['Wheelchair users zip-tied at Senate Medicaid-cuts protest', 'https://www.wusa9.com/article/news/local/dc/protestors-arrested-russell-senate-office-building/65-973a0d86-65ad-4658-bdae-e72343070601', 'WUSA9', '2025-06-25', null, null, null],

        // ── Disability rights / Medicaid · Rayburn House Office Building, DC · May 13, 2025 ──
        ['25 arrested protesting Medicaid cuts at House hearing', 'https://www.axios.com/2025/05/13/capitol-police-arrest-protesters-medicaid-budget', 'Axios', '2025-05-13', 38.8867704, -77.0100669, 'Rayburn House Office Building, Washington, DC'],
        ['Activists arrested outside Medicaid hearing at the Capitol', 'https://www.deseret.com/politics/2025/05/13/protesters-arrested-outside-medicaid-meeting/', 'Deseret News', '2025-05-13', null, null, null],

        // ── Labor · Empire State Building, Manhattan NY · Dec 4, 2025 (Starbucks strike) ──
        ['12 Starbucks workers arrested in Empire State Building sit-in', 'https://www.democracynow.org/2025/12/5/headlines/12_arrested_as_striking_starbucks_workers_hold_sit_in_protest_at_empire_state_building', 'Democracy Now!', '2025-12-04', 40.7484421, -73.9856589, 'Empire State Building, Manhattan, NY'],
        ['12 striking Starbucks workers arrested at Empire State Building', 'https://abc7ny.com/post/12-starbucks-workers-arrested-protesting-outside-empire-state-building-manhattan/18253341/', 'ABC7 New York', '2025-12-04', null, null, null],

        // ── Labor · Starbucks Roasting Plant, York County PA · Dec 17, 2025 ──
        ['Starbucks workers arrested blocking York roasting plant', 'https://paydayreport.com/border-patrol-raids-picket-line-starbucks-workers-arrested-at-roasting-plant-ghiradelli-workers-move-to-strike/', 'Payday Report', '2025-12-17', 40.0516630, -76.7393755, 'Starbucks Roasting Plant, York County, PA'],

        // ── Voting rights · Florida State Capitol, Tallahassee · May 15, 2026 ──
        ['Rep. Angie Nixon arrested after 5-hour Florida Capitol sit-in', 'https://www.wlrn.org/government-politics/2026-05-15/democratic-state-rep-angie-nixon-arrested-after-5-hour-protest-at-florida-capitol', 'WLRN', '2026-05-15', 30.4381743, -84.2821703, 'Florida State Capitol, Tallahassee, FL'],

        // ── Voting rights · Tennessee State Capitol, Nashville · May 7, 2026 ──
        ['Three arrested at Tennessee Capitol over Memphis redistricting', 'https://nashvillebanner.com/2026/05/07/tennessee-congressional-redistricting-confederate-flag/', 'Nashville Banner', '2026-05-07', 36.1658290, -86.7842374, 'Tennessee State Capitol, Nashville, TN'],

        // ── Press freedom · Cities Church, St. Paul MN · Jan 30, 2026 ──
        ['Journalists Don Lemon and Georgia Fort arrested over protest coverage', 'https://www.aljazeera.com/news/2026/1/30/journalist-don-lemon-arrested-in-connection-to-minnesota-ice-protest', 'Al Jazeera', '2026-01-30', 44.9409935, -93.1648789, 'Cities Church, St. Paul, MN'],
        ['Don Lemon arrested by federal authorities, attorney says', 'https://www.nbcnews.com/news/us-news/don-lemon-arrested-federal-authorities-attorney-says-rcna256680', 'NBC News', '2026-01-30', null, null, null],
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
