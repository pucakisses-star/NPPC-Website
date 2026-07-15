<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds dashboard newswire/map entries for states that had no prior
 * representation on the tracker. Sourced from public reporting and matched
 * on URL (updateOrCreate) so the command is idempotent and re-runnable.
 *
 * Covers the six states that had no prior representation on the tracker:
 * Delaware, North Dakota, Wyoming, Hawaii, New Hampshire, and Rhode Island.
 * Delaware's HB 182 (June 30) falls inside the mid-June–mid-July 2026 window;
 * the other five carry their true (spring 2026) dates from the reporting, added
 * to fill the geographic gaps.
 */
final class AddUnrepresentedStatesDashboardCases extends Command
{
    protected $signature = 'dashboard:add-unrepresented-states';

    protected $description = 'Add dashboard entries for previously-unrepresented states';

    public function handle(): int
    {
        $cases = [
            [
                'url'            => 'https://www.aclu-de.org/news/delaware-banned-287g-agreements-ice-heres-what-must-happen-next/',
                'title'          => 'Delaware General Assembly passes HB 182, banning local police 287(g) agreements with ICE',
                'source'         => 'ACLU of Delaware',
                'category'       => 'other',
                'published_at'   => '2026-06-30',
                'location_label' => 'Dover, DE',
                'lat'            => 39.1573,
                'lng'            => -75.5197,
            ],
            [
                'url'            => 'https://ictnews.org/news/a-decade-after-standing-rock-protests-contentious-segment-of-dakota-access-oil-pipeline-gets-ok/',
                'title'          => 'Army Corps grants the Dakota Access easement under Lake Oahe; Standing Rock Sioux vow to fight for their treaty rights',
                'source'         => 'ICT',
                'category'       => 'other',
                'published_at'   => '2026-05-21',
                'location_label' => 'Standing Rock, ND',
                'lat'            => 46.0906,
                'lng'            => -100.6754,
            ],
            [
                'url'            => 'https://wyofile.com/detaining-jackson-hole-immigrants-ice-agents-target-western-wyoming/',
                'title'          => 'ICE agents target Jackson Hole and western Wyoming, detaining immigrants for transfer to out-of-state facilities',
                'source'         => 'WyoFile',
                'category'       => 'arrest',
                'published_at'   => '2026-05-15',
                'location_label' => 'Jackson, WY',
                'lat'            => 43.4799,
                'lng'            => -110.7624,
            ],
            [
                'url'            => 'https://www.civilbeat.org/2026/04/locked-up-too-long-legal-tactic-challenges-hawaii-ice-detentions/',
                'title'          => 'Habeas petitions challenge prolonged ICE detentions in Hawaii as arrests spike statewide',
                'source'         => 'Honolulu Civil Beat',
                'category'       => 'prosecution',
                'published_at'   => '2026-04-30',
                'location_label' => 'Honolulu, HI',
                'lat'            => 21.3069,
                'lng'            => -157.8583,
            ],
            [
                'url'            => 'https://www.aclu-nh.org/press-releases/released-aclu-nh-client-ice-detention-strafford-county-let-go-after-more-month/',
                'title'          => 'ACLU-NH client released from ICE detention at Strafford County jail after more than a month held',
                'source'         => 'ACLU of New Hampshire',
                'category'       => 'other',
                'published_at'   => '2026-04-20',
                'location_label' => 'Dover, NH',
                'lat'            => 43.1979,
                'lng'            => -70.8737,
            ],
            [
                'url'            => 'https://www.wpri.com/news/us-and-world/no-kings-protests-taking-place-across-ri-mass/',
                'title'          => 'Thousands rally at the Rhode Island State House in the "No Kings" day of action against ICE and the Trump administration',
                'source'         => 'WPRI',
                'category'       => 'protest',
                'published_at'   => '2026-03-28',
                'location_label' => 'Providence, RI',
                'lat'            => 41.8309,
                'lng'            => -71.4148,
            ],
        ];

        $added = 0;
        foreach ($cases as $c) {
            $link = DashboardLink::updateOrCreate(
                ['url' => $c['url']],
                [
                    'title'          => $c['title'],
                    'source'         => $c['source'],
                    'category'       => $c['category'],
                    'published_at'   => Carbon::parse($c['published_at']),
                    'location_label' => $c['location_label'],
                    'lat'            => $c['lat'],
                    'lng'            => $c['lng'],
                ],
            );
            $this->line(($link->wasRecentlyCreated ? '  Added: ' : '  Refreshed: ').$link->title);
            $added++;
        }

        $this->info("\nDone. Processed {$added} dashboard "
            .($added === 1 ? 'entry' : 'entries').'.');

        return self::SUCCESS;
    }
}
