<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the journalist arrest/detention incidents from the U.S. Press Freedom
 * Tracker's arrest-and-criminal-charge index that were missing from the
 * dashboard: three reporters kettled by NYPD while covering a May 5, 2026
 * protest near a NYC synagogue, and four reporters ticketed/detained in LAPD
 * kettles at the May 1, 2026 May Day protests in Los Angeles. (Nick Stern's
 * LA arrest from the same set was already on the dashboard.) All categorized
 * "arrest"; source label matches the dashboard's existing Press Freedom
 * Tracker entries. Coordinates nudged so the per-city pins don't overlap.
 * Matched on URL with updateOrCreate, so the command is idempotent.
 */
class AddPressFreedomKettleDashboardCases extends Command {
    protected $signature = 'dashboard:add-press-freedom-kettle-cases';
    protected $description = 'Add the missing Press Freedom Tracker journalist-kettle arrests (NYC synagogue + LA May Day) to the dashboard';

    public function handle(): int {
        $cases = [
            // NYC synagogue protest — May 5, 2026
            [
                'title'          => 'Journalist kettled while covering protest near NYC synagogue',
                'url'            => 'https://pressfreedomtracker.us/all-incidents/journalist-kettled-while-covering-protest-near-nyc-synagogue/',
                'source'         => 'U.S. Press Freedom Tracker',
                'category'       => 'arrest',
                'published_at'   => '2026-05-05',
                'location_label' => 'New York, NY',
                'lat'            => 40.7130,
                'lng'            => -74.0058,
            ],
            [
                'title'          => 'Photojournalist kettled while covering protest near NYC synagogue',
                'url'            => 'https://pressfreedomtracker.us/all-incidents/photojournalist-kettled-while-covering-protest-near-nyc-synagogue/',
                'source'         => 'U.S. Press Freedom Tracker',
                'category'       => 'arrest',
                'published_at'   => '2026-05-05',
                'location_label' => 'New York, NY',
                'lat'            => 40.7126,
                'lng'            => -74.0062,
            ],
            [
                'title'          => 'Journalist held in NYPD kettle while covering protest near synagogue',
                'url'            => 'https://pressfreedomtracker.us/all-incidents/journalist-held-in-nypd-kettle-while-covering-protest-near-synagogue/',
                'source'         => 'U.S. Press Freedom Tracker',
                'category'       => 'arrest',
                'published_at'   => '2026-05-05',
                'location_label' => 'New York, NY',
                'lat'            => 40.7132,
                'lng'            => -74.0055,
            ],
            // LA May Day protests — May 1, 2026
            [
                'title'          => 'Reporter ticketed, jabbed with police baton at LA protest',
                'url'            => 'https://pressfreedomtracker.us/all-incidents/reporter-ticketed-jabbed-with-police-baton-at-la-protest/',
                'source'         => 'U.S. Press Freedom Tracker',
                'category'       => 'arrest',
                'published_at'   => '2026-05-01',
                'location_label' => 'Los Angeles, CA',
                'lat'            => 34.0524,
                'lng'            => -118.2440,
            ],
            [
                'title'          => 'Reporter detained in police kettle at May Day protest in LA',
                'url'            => 'https://pressfreedomtracker.us/all-incidents/reporter-detained-in-police-kettle-at-may-day-protest-in-la/',
                'source'         => 'U.S. Press Freedom Tracker',
                'category'       => 'arrest',
                'published_at'   => '2026-05-01',
                'location_label' => 'Los Angeles, CA',
                'lat'            => 34.0520,
                'lng'            => -118.2434,
            ],
            [
                'title'          => 'Reporter detained in LAPD kettle amid May Day protests',
                'url'            => 'https://pressfreedomtracker.us/all-incidents/reporter-detained-in-lapd-kettle-amid-may-day-protests/',
                'source'         => 'U.S. Press Freedom Tracker',
                'category'       => 'arrest',
                'published_at'   => '2026-05-01',
                'location_label' => 'Los Angeles, CA',
                'lat'            => 34.0526,
                'lng'            => -118.2442,
            ],
            [
                'title'          => 'Independent journalist detained in LAPD kettle at May Day protest',
                'url'            => 'https://pressfreedomtracker.us/all-incidents/independent-journalist-detained-in-lapd-kettle-at-may-day-protest/',
                'source'         => 'U.S. Press Freedom Tracker',
                'category'       => 'arrest',
                'published_at'   => '2026-05-01',
                'location_label' => 'Los Angeles, CA',
                'lat'            => 34.0518,
                'lng'            => -118.2430,
            ],
        ];

        $created = 0;
        $updated = 0;
        foreach ($cases as $case) {
            $link = DashboardLink::updateOrCreate(
                ['url' => $case['url']],
                array_merge($case, ['published_at' => Carbon::parse($case['published_at'])]),
            );

            if ($link->wasRecentlyCreated) {
                $created++;
                $this->info("Added: {$case['title']}");
            } else {
                $updated++;
                $this->line("Updated: {$case['title']}");
            }
        }

        $this->info("Done. {$created} added, {$updated} updated.");

        return self::SUCCESS;
    }
}
