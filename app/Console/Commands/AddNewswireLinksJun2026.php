<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Batch-add a set of dashboard newswire links shared in late June 2026. Each was
 * resolved from a shortened (t.co / buff.ly / interc.pt / dlvr.it / bit.ly /
 * ebx.sh) or direct link to its canonical article URL. Matched on URL with
 * updateOrCreate, so the command is idempotent. Located incidents carry
 * coordinates so they also plot on the dashboard map.
 */
final class AddNewswireLinksJun2026 extends Command
{
    protected $signature = 'dashboard:add-newswire-jun2026';

    protected $description = 'Add the late-June 2026 batch of dashboard newswire links';

    public function handle(): int
    {
        $cases = [
            [
                'title' => 'Judge temporarily blocks subpoenas in criminal probe of transgender care at New York hospitals',
                'url' => 'https://www.nbcnews.com/news/us-news/judge-temporarily-blocks-subpoenas-criminal-probe-transgender-care-new-rcna351612',
                'source' => 'NBC News',
                'category' => 'other',
                'published_at' => '2026-06-24',
                'location_label' => 'New York, NY',
                'lat' => 40.7128, 'lng' => -74.0060,
            ],
            [
                'title' => 'Trump Loyalist Bill Pulte Begins Purge at National Intelligence Office',
                'url' => 'https://www.democracynow.org/2026/6/24/headlines/trump_loyalist_bill_pulte_begins_purge_at_national_intelligence_office',
                'source' => 'Democracy Now!',
                'category' => 'other',
                'published_at' => '2026-06-24',
            ],
            [
                'title' => "Public Records Show FBI Secretly Extracted Data From ICE Protesters' Phones",
                'url' => 'https://truthout.org/articles/public-records-show-fbi-secretly-extracted-data-from-ice-protesters-phones/',
                'source' => 'Truthout',
                'category' => 'other',
                'published_at' => '2026-06-24',
                'location_label' => 'Spokane, WA',
                'lat' => 47.6588, 'lng' => -117.4260,
            ],
            [
                'title' => 'The Intercept Sues to Uncover Secretive Government Anti-Protester Database',
                'url' => 'https://theintercept.com/2026/06/24/intercept-lawsuit-ice-protesters-surveillance-travel/',
                'source' => 'The Intercept',
                'category' => 'other',
                'published_at' => '2026-06-24',
            ],
            [
                'title' => 'Anti-ICE Protesters Sentenced to Decades in Prison Over "Terrorism" at Texas ICE Jail',
                'url' => 'https://www.democracynow.org/2026/6/24/headlines/anti_ice_protesters_sentenced_to_decades_in_prison_over_terrorism_at_texas_ice_jail',
                'source' => 'Democracy Now!',
                'category' => 'prosecution',
                'published_at' => '2026-06-24',
                'location_label' => 'Alvarado, TX',
                'lat' => 32.4068, 'lng' => -97.2114,
            ],
            [
                'title' => 'Oil & Gas Workers Association Founder Matt Coday Faces Arrest Warrant',
                'url' => 'https://thetexan.news/issues/criminal-justice/oil-gas-workers-association-founder-matt-coday-faces-arrest-warrant/article_6eaea73a-9083-4da3-9471-47a152818781.html',
                'source' => 'The Texan',
                'category' => 'arrest',
                'published_at' => '2026-06-15',
                'location_label' => 'Odessa, TX',
                'lat' => 31.8457, 'lng' => -102.3676,
            ],
            [
                'title' => "Prosecutors delay appeal in former journalist Timothy Burke's criminal hacking case",
                'url' => 'https://thedesk.net/2026/06/timothy-burke-case-prosecutors-delay-appeal-numerous-times/',
                'source' => 'The Desk',
                'category' => 'other',
                'published_at' => '2026-06-24',
                'location_label' => 'Tampa, FL',
                'lat' => 27.9506, 'lng' => -82.4572,
            ],
            [
                'title' => 'Rep. LaMonica McIver urges appeals court to toss federal assault charges',
                'url' => 'https://www.politico.com/news/2026/06/24/mciver-new-jersey-delaware-00973844',
                'source' => 'Politico',
                'category' => 'prosecution',
                'published_at' => '2026-06-24',
                'location_label' => 'Newark, NJ',
                'lat' => 40.7357, 'lng' => -74.1724,
            ],
            [
                'title' => 'Poetica Coffee Draws Protests After Dan Goldman Ban Over Israel Stance',
                'url' => 'https://www.businessinsider.com/poetica-coffee-brooklyn-protest-dan-goldman-israel-2026-6',
                'source' => 'Business Insider',
                'category' => 'protest',
                'published_at' => '2026-06-24',
                'location_label' => 'Brooklyn, NY',
                'lat' => 40.6782, 'lng' => -73.9442,
            ],
            [
                'title' => "Former Delco woman tied to the Zizians extremist group charged with her parents' murder",
                'url' => 'https://www.inquirer.com/crime/michelle-zajko-zizians-group-parents-murder-charges-chester-heights-20260624.html',
                'source' => 'The Philadelphia Inquirer',
                'category' => 'prosecution',
                'published_at' => '2026-06-24',
                'location_label' => 'Chester Heights, PA',
                'lat' => 39.8993, 'lng' => -75.4321,
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
                $this->line("Refreshed: {$case['title']}");
            }
        }

        $this->info("\nDone. {$created} added, {$updated} refreshed.");

        return self::SUCCESS;
    }
}
