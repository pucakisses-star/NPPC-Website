<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds notable US Capitol-complex protest ACTIONS that did NOT result in arrests
 * (rallies, marches, deliveries) as DashboardLink "protest" markers. Distinct
 * from the arrest/prosecution cases. In-window (on/after May 7, 2025), sourced
 * from public reporting; matched on URL so the command is idempotent.
 */
class AddCapitolActionsDashboardCases extends Command {
    protected $signature = 'dashboard:add-capitol-actions';
    protected $description = 'Add notable US Capitol protest actions that did not result in arrests';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'Rep. Rashida Tlaib, doctors, and veterans rallied at the US Capitol banging empty pots over starvation in Gaza (no arrests)',
                'url'            => 'https://www.aljazeera.com/news/2025/7/24/us-doctors-veterans-urge-trump-to-end-israel-support-as-hunger-grips-gaza',
                'source'         => 'Al Jazeera',
                'category'       => 'protest',
                'published_at'   => '2025-07-24',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8870,
                'lng'            => -77.0090,
            ],
            [
                'title'          => 'WorldPride International March on Washington marched from the Lincoln Memorial to the US Capitol for LGBTQ and trans rights (peaceful, no arrests)',
                'url'            => 'https://www.washingtonpost.com/dc-md-va/2025/06/08/world-pride-rally-march-national-mall/',
                'source'         => 'Washington Post',
                'category'       => 'protest',
                'published_at'   => '2025-06-08',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8895,
                'lng'            => -77.0080,
            ],
            [
                'title'          => 'Free DC residents delivered letters to congressional offices urging an end to the federal "occupation" of Washington, DC (no arrests)',
                'url'            => 'https://abcnews.go.com/Politics/washington-dc-residents-press-congress-end-trumps-federal/story?id=125275192',
                'source'         => 'ABC News',
                'category'       => 'protest',
                'published_at'   => '2025-09-04',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8920,
                'lng'            => -77.0080,
            ],
            [
                'title'          => 'Hakeem Jeffries and a dozen-plus lawmakers joined the "Healthcare Over Billionaires" rally on the US Capitol steps against ACA and Medicaid cuts (no arrests)',
                'url'            => 'https://www.newsfromthestates.com/article/protesters-us-capitol-back-democrats-shutdown-fight-over-health-care-costs',
                'source'         => 'News From The States',
                'category'       => 'protest',
                'published_at'   => '2025-09-30',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8895,
                'lng'            => -77.0105,
            ],
            [
                'title'          => 'About 100,000 rallied near the US Capitol for the "No Kings" national day of protest; DC police reported no arrests',
                'url'            => 'https://www.pbs.org/newshour/politics/watch-live-no-kings-rally-in-dc-on-nationwide-day-of-protests-against-trump-administration',
                'source'         => 'PBS NewsHour',
                'category'       => 'protest',
                'published_at'   => '2025-10-18',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8915,
                'lng'            => -77.0130,
            ],
        ];

        $created = 0;
        foreach ($cases as $case) {
            $link = DashboardLink::firstOrCreate(
                ['url' => $case['url']],
                array_merge($case, ['published_at' => Carbon::parse($case['published_at'])]),
            );

            if ($link->wasRecentlyCreated) {
                $created++;
                $this->info("Added: {$case['title']}");
            } else {
                $this->line("Skipped (already present): {$case['title']}");
            }
        }

        $this->info("Done. {$created} new action(s) added; ".(count($cases) - $created).' already present.');

        return self::SUCCESS;
    }
}
