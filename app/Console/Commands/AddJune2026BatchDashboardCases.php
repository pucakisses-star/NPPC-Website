<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds a batch of June 2026 civil-liberties / repression items to the
 * dashboard:
 *
 *  - Two former Logan, Utah Justice Court clerks federally charged with
 *    helping immigrants evade an ICE agent (Salt Lake Tribune + KSL).
 *  - San Diego ICE-enforcement surges and Unión del Barrio community
 *    watch patrols (Bolts).
 *  - The FBI raid on the Ohio Organizing Collaborative, a Cleveland
 *    voting-rights / pro-democracy group (MS NOW).
 *  - The retaliatory ~3,000-mile transfer of Malik Muhammad — the
 *    longest-sentenced 2020 Portland protester — to a South Carolina
 *    prison (The Intercept).
 *
 * Matched on URL with updateOrCreate, so the command is idempotent.
 */
class AddJune2026BatchDashboardCases extends Command {
    protected $signature = 'dashboard:add-june-2026-batch';
    protected $description = 'Add a June 2026 batch of repression items (Logan clerks, San Diego patrols, Ohio FBI raid, Malik Muhammad transfer)';

    public function handle(): int {
        $cases = [
            [
                'title'          => '2 former city employees in Utah face federal charges for allegedly helping someone evade ICE',
                'url'            => 'https://www.sltrib.com/news/2026/06/11/former-logan-employees-face/',
                'source'         => 'The Salt Lake Tribune',
                'category'       => 'prosecution',
                'published_at'   => '2026-06-11',
                'location_label' => 'Logan, UT',
                'lat'            => 41.7370,
                'lng'            => -111.8338,
            ],
            [
                'title'          => 'Ex-Logan Justice Court clerks charged after allegedly helping immigrant evade ICE',
                'url'            => 'https://www.ksl.com/article/51509501/ex-logan-justice-court-clerks-charged-after-allegedly-helping-immigrant-evade-ice',
                'source'         => 'KSL',
                'category'       => 'prosecution',
                'published_at'   => '2026-06-10',
                'location_label' => 'Logan, UT',
                'lat'            => 41.7363,
                'lng'            => -111.8329,
            ],
            [
                'title'          => 'Amid violent ICE surges in San Diego, volunteer patrols fill the gap left by California\'s sanctuary law',
                'url'            => 'https://boltsmag.org/violent-ice-surges-san-diego-volunteer-patrols-union-del-barrio-california-sanctuary-policy/',
                'source'         => 'Bolts',
                'category'       => 'other',
                'published_at'   => '2026-06-03',
                'location_label' => 'San Diego, CA',
                'lat'            => 32.7157,
                'lng'            => -117.1611,
            ],
            [
                'title'          => 'Ohio pro-democracy organization raided by FBI',
                'url'            => 'https://www.ms.now/news/ohio-pro-democracy-organization-raided-by-fbi',
                'source'         => 'MS NOW',
                'category'       => 'other',
                'published_at'   => '2026-06-11',
                'location_label' => 'Cleveland, OH',
                'lat'            => 41.4993,
                'lng'            => -81.6944,
            ],
            [
                'title'          => 'Malik Muhammad, Oregon\'s longest-sentenced 2020 protester, was transferred 3,000 miles to a South Carolina prison',
                'url'            => 'https://theintercept.com/2026/06/08/malik-muhammad-prison-oregon-south-carolina/',
                'source'         => 'The Intercept',
                'category'       => 'other',
                'published_at'   => '2026-06-08',
                'location_label' => 'Portland, OR',
                'lat'            => 45.5152,
                'lng'            => -122.6784,
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
